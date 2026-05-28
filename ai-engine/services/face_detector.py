"""
SIHADIR — Face Detector Service
Detects faces in images using OpenCV + DeepFace.
"""
import logging
from typing import Optional, Tuple

import cv2
import numpy as np
from deepface import DeepFace

logger = logging.getLogger(__name__)


class FaceDetectorService:
    """
    Responsible for:
    - Validating that an image contains exactly one detectable face.
    - Returning the cropped face region (aligned).
    """

    def __init__(self, detector_backend: str = 'opencv'):
        self.detector_backend = detector_backend

    def detect(self, image_array: np.ndarray) -> Tuple[bool, Optional[np.ndarray], str]:
        """
        Detect face in image.

        Returns:
            (success, cropped_face_or_None, message)
        """
        try:
            # DeepFace.extract_faces returns list of face objects
            faces = DeepFace.extract_faces(
                img_path=image_array,
                detector_backend=self.detector_backend,
                enforce_detection=True,
                align=True
            )

            if not faces:
                return False, None, "Tidak ada wajah terdeteksi dalam gambar."

            if len(faces) > 1:
                return False, None, "Terdeteksi lebih dari satu wajah. Pastikan hanya satu orang."

            face_obj = faces[0]
            face_img = face_obj['face']

            # Convert float [0,1] → uint8 [0,255] if needed
            if face_img.dtype != np.uint8:
                face_img = (face_img * 255).astype(np.uint8)

            confidence = face_obj.get('confidence', 1.0)
            if confidence < 0.70:
                return False, None, f"Kualitas deteksi wajah rendah ({confidence:.0%}). Coba lagi."

            logger.debug(f"Face detected with confidence {confidence:.3f}")
            return True, face_img, "Wajah terdeteksi."

        except ValueError as e:
            logger.warning(f"Face detection failed: {e}")
            return False, None, "Tidak ada wajah terdeteksi. Pastikan wajah terlihat jelas."
        except Exception as e:
            logger.error(f"Unexpected error in face detection: {e}", exc_info=True)
            return False, None, f"Error deteksi wajah: {str(e)}"

    @staticmethod
    def load_image_from_bytes(image_bytes: bytes) -> Optional[np.ndarray]:
        """Load image bytes into numpy array (BGR)."""
        try:
            nparr = np.frombuffer(image_bytes, np.uint8)
            img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
            if img is None:
                return None
            return img
        except Exception as e:
            logger.error(f"Failed to load image: {e}")
            return None
