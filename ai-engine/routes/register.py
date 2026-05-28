"""
SIHADIR — Face Registration Route
POST /api/register

Registers a student's face embedding into the database.
Called by admin when adding/updating a student's face data.

Accepts: multipart/form-data
  - image (file, required): clear face photo
  - student_id (int, required): student's database ID
  - student_name (str, optional): for logging

Returns JSON with registration result.
"""
import logging
import time

from flask import Blueprint, current_app, jsonify, request

from services.face_detector import FaceDetectorService
from services.face_recognizer import FaceRecognizerService
from services.embedding_store import EmbeddingStoreService
from utils.image_utils import resize_image

register_bp = Blueprint('register', __name__)
logger = logging.getLogger(__name__)

_detector = None
_recognizer = None
_store = None


def _get_services():
    global _detector, _recognizer, _store
    if _detector is None:
        cfg = current_app.config
        _detector = FaceDetectorService(
            detector_backend=cfg.get('FACE_DETECTOR', 'opencv')
        )
        _recognizer = FaceRecognizerService(
            model_name=cfg.get('FACE_MODEL', 'ArcFace'),
            confidence_threshold=cfg.get('CONFIDENCE_THRESHOLD', 0.80)
        )
        _store = EmbeddingStoreService()
    return _detector, _recognizer, _store


@register_bp.route('/register', methods=['POST'])
def register_face():
    """Register a student's face embedding."""
    start_ms = time.time()

    # ── 1. Validate inputs ─────────────────────────────────────
    if 'image' not in request.files:
        return jsonify({'status': 'error', 'message': 'Field "image" diperlukan.'}), 400

    student_id_raw = request.form.get('student_id', '')
    if not student_id_raw or not student_id_raw.isdigit():
        return jsonify({'status': 'error', 'message': 'Field "student_id" (integer) diperlukan.'}), 400

    student_id = int(student_id_raw)
    student_name = request.form.get('student_name', f'Student #{student_id}')

    file = request.files['image']
    image_bytes = file.read()
    if len(image_bytes) == 0:
        return jsonify({'status': 'error', 'message': 'File gambar kosong.'}), 400

    # ── 2. Load & process image ────────────────────────────────
    detector, recognizer, store = _get_services()
    image = FaceDetectorService.load_image_from_bytes(image_bytes)
    if image is None:
        return jsonify({'status': 'error', 'message': 'Gagal memproses gambar.'}), 400

    image = resize_image(image, max_size=640)

    # ── 3. Detect face ─────────────────────────────────────────
    detected, face_img, det_msg = detector.detect(image)
    if not detected:
        return jsonify({'status': 'error', 'message': det_msg}), 200

    # ── 4. Extract embedding ───────────────────────────────────
    success, embedding, emb_msg = recognizer.extract_embedding(face_img)
    if not success:
        return jsonify({'status': 'error', 'message': emb_msg}), 200

    # ── 5. Save to database ────────────────────────────────────
    saved, save_msg, embedding_id = store.save_embedding(
        student_id=student_id,
        embedding=embedding,
        model_used=current_app.config.get('FACE_MODEL', 'ArcFace')
    )

    processing_ms = int((time.time() - start_ms) * 1000)

    if saved:
        logger.info(
            f"Face registered: student_id={student_id}, name={student_name}, "
            f"embedding_id={embedding_id}, time={processing_ms}ms"
        )
        return jsonify({
            'status': 'success',
            'student_id': student_id,
            'student_name': student_name,
            'embedding_id': embedding_id,
            'model': current_app.config.get('FACE_MODEL', 'ArcFace'),
            'processing_time_ms': processing_ms,
            'message': f'Wajah {student_name} berhasil didaftarkan.'
        }), 200
    else:
        return jsonify({'status': 'error', 'message': save_msg}), 500


@register_bp.route('/register/<int:student_id>', methods=['DELETE'])
def delete_face(student_id: int):
    """Delete a student's face embedding."""
    _, _, store = _get_services()
    success, message = store.delete_embedding(student_id)
    if success:
        logger.info(f"Embedding deleted for student_id={student_id}")
        return jsonify({'status': 'success', 'message': message}), 200
    return jsonify({'status': 'error', 'message': message}), 404
