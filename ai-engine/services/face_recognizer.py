"""
SIHADIR — Face Recognizer Service
Extracts embeddings with DeepFace (ArcFace) and matches against database.
"""
import logging
from typing import Dict, List, Optional, Tuple

import numpy as np
from deepface import DeepFace

from services.embedding_store import EmbeddingStoreService

logger = logging.getLogger(__name__)


class FaceRecognizerService:
    """
    Responsible for:
    - Extracting 512-dim ArcFace embedding from a face image.
    - Comparing against all stored embeddings.
    - Returning best match with confidence score.
    """

    def __init__(
        self,
        model_name: str = 'ArcFace',
        confidence_threshold: float = 0.80
    ):
        self.model_name = model_name
        self.confidence_threshold = confidence_threshold
        self.embedding_store = EmbeddingStoreService()
        logger.info(
            f"FaceRecognizerService initialized: model={model_name}, "
            f"threshold={confidence_threshold}"
        )

    def extract_embedding(self, face_image: np.ndarray) -> Tuple[bool, Optional[List[float]], str]:
        """
        Extract face embedding from a cropped face image.

        Returns: (success, embedding_or_None, message)
        """
        try:
            result = DeepFace.represent(
                img_path=face_image,
                model_name=self.model_name,
                enforce_detection=False,
                detector_backend='skip'  # Already detected by FaceDetector
            )

            if not result:
                return False, None, "Gagal mengekstrak fitur wajah."

            embedding = result[0]['embedding']
            logger.debug(f"Embedding extracted, dim={len(embedding)}")
            return True, embedding, "Fitur wajah berhasil diekstrak."

        except Exception as e:
            logger.error(f"Embedding extraction failed: {e}", exc_info=True)
            return False, None, f"Error ekstraksi embedding: {str(e)}"

    def recognize(
        self,
        face_image: np.ndarray
    ) -> Dict:
        """
        Recognize a face against all stored embeddings.

        Returns dict with:
          - status: 'recognized' | 'unknown' | 'error'
          - student_id: int or None
          - confidence: float (0–1)
          - message: str
        """
        # Step 1: Extract embedding
        success, query_embedding, msg = self.extract_embedding(face_image)
        if not success:
            return {
                'status': 'error',
                'student_id': None,
                'confidence': 0.0,
                'message': msg
            }

        # Step 2: Load all stored embeddings
        stored = self.embedding_store.load_all_embeddings()
        if not stored:
            return {
                'status': 'unknown',
                'student_id': None,
                'confidence': 0.0,
                'message': 'Belum ada data wajah terdaftar di sistem.'
            }

        # Step 3: Find best match via cosine similarity
        best_score = -1.0
        best_student_id = None

        for student_id, stored_embedding in stored:
            score = EmbeddingStoreService.cosine_similarity(
                query_embedding, stored_embedding
            )
            if score > best_score:
                best_score = score
                best_student_id = student_id

        logger.info(
            f"Best match: student_id={best_student_id}, "
            f"score={best_score:.4f}, threshold={self.confidence_threshold}"
        )

        # Step 4: Apply threshold
        if best_score >= self.confidence_threshold:
            return {
                'status': 'recognized',
                'student_id': best_student_id,
                'confidence': round(best_score, 4),
                'message': 'Wajah berhasil dikenali.'
            }
        else:
            return {
                'status': 'unknown',
                'student_id': None,
                'confidence': round(best_score, 4),
                'message': (
                    f'Wajah tidak dikenali. '
                    f'Skor terbaik: {best_score:.1%} (threshold: {self.confidence_threshold:.0%})'
                )
            }
