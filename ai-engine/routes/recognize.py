"""
SIHADIR — Recognition Route
POST /api/recognize

Accepts: multipart/form-data
  - image (file, required): photo to recognize
  - session_id (str, optional): for logging

Returns JSON with recognition result.
"""
import time
import logging

from flask import Blueprint, current_app, jsonify, request

from models.db import db, RecognitionLogModel
from services.face_detector import FaceDetectorService
from services.face_recognizer import FaceRecognizerService
from services.liveness import LivenessService
from utils.image_utils import compute_hash, resize_image

recognize_bp = Blueprint('recognize', __name__)
logger = logging.getLogger(__name__)

# Service instances (initialized lazily)
_detector = None
_recognizer = None
_liveness = None


def _get_services():
    global _detector, _recognizer, _liveness
    if _detector is None:
        cfg = current_app.config
        _detector = FaceDetectorService(
            detector_backend=cfg.get('FACE_DETECTOR', 'opencv')
        )
        _recognizer = FaceRecognizerService(
            model_name=cfg.get('FACE_MODEL', 'ArcFace'),
            confidence_threshold=cfg.get('CONFIDENCE_THRESHOLD', 0.80)
        )
        _liveness = LivenessService(
            blur_threshold=cfg.get('LIVENESS_BLUR_THRESHOLD', 50)
        )
    return _detector, _recognizer, _liveness


@recognize_bp.route('/recognize', methods=['POST'])
def recognize():
    """Main face recognition endpoint."""
    start_ms = time.time()

    # ── 1. Validate request ────────────────────────────────────
    if 'image' not in request.files:
        return jsonify({
            'status': 'error',
            'message': 'Field "image" tidak ditemukan dalam request.'
        }), 400

    file = request.files['image']
    if file.filename == '':
        return jsonify({
            'status': 'error',
            'message': 'Tidak ada file yang dipilih.'
        }), 400

    image_bytes = file.read()
    if len(image_bytes) == 0:
        return jsonify({
            'status': 'error',
            'message': 'File gambar kosong.'
        }), 400

    image_hash = compute_hash(image_bytes)
    session_id = request.form.get('session_id', '')

    # ── 2. Load image ──────────────────────────────────────────
    detector, recognizer, liveness = _get_services()
    image = FaceDetectorService.load_image_from_bytes(image_bytes)
    if image is None:
        _log_result(None, image_hash, 'error', 0.0,
                    int((time.time() - start_ms) * 1000),
                    'Failed to decode image')
        return jsonify({
            'status': 'error',
            'message': 'Gagal memproses gambar. Pastikan format file valid (JPG/PNG).'
        }), 400

    image = resize_image(image, max_size=640)

    # ── 3. Liveness check ──────────────────────────────────────
    liveness_result = liveness.check(image)
    if not liveness_result['is_live']:
        _log_result(None, image_hash, 'spoofing', 0.0,
                    int((time.time() - start_ms) * 1000),
                    liveness_result['message'])
        return jsonify({
            'status': 'spoofing',
            'liveness_score': liveness_result['score'],
            'message': liveness_result['message']
        }), 200

    # ── 4. Face detection ──────────────────────────────────────
    detected, face_img, det_msg = detector.detect(image)
    if not detected:
        _log_result(None, image_hash, 'no_face', 0.0,
                    int((time.time() - start_ms) * 1000), det_msg)
        return jsonify({
            'status': 'no_face',
            'message': det_msg
        }), 200

    # ── 5. Face recognition ────────────────────────────────────
    result = recognizer.recognize(face_img)
    processing_ms = int((time.time() - start_ms) * 1000)

    log_result = result['status'] if result['status'] in ['recognized', 'unknown'] else 'error'
    _log_result(
        result.get('student_id'),
        image_hash,
        log_result,
        result.get('confidence', 0.0),
        processing_ms,
        result.get('message') if result['status'] != 'recognized' else None
    )

    logger.info(
        f"Recognize: status={result['status']}, "
        f"student={result.get('student_id')}, "
        f"confidence={result.get('confidence', 0):.3f}, "
        f"time={processing_ms}ms, session={session_id}"
    )

    return jsonify({
        **result,
        'processing_time_ms': processing_ms,
        'liveness_score': liveness_result['score']
    }), 200


def _log_result(student_id, image_hash, result, confidence, processing_ms, error_msg=None):
    """Write recognition attempt to audit log."""
    try:
        log = RecognitionLogModel(
            student_id=student_id,
            image_hash=image_hash,
            result=result,
            confidence_score=confidence,
            processing_time_ms=processing_ms,
            error_message=error_msg
        )
        db.session.add(log)
        db.session.commit()
    except Exception as e:
        db.session.rollback()
        logger.warning(f"Failed to write recognition log: {e}")
