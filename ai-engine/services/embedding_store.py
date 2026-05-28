"""
SIHADIR — Embedding Store Service
Manages saving and loading of face embeddings from MySQL.
"""
import json
import logging
from typing import List, Optional, Tuple

import numpy as np

logger = logging.getLogger(__name__)


class EmbeddingStoreService:
    """
    Stores and retrieves face embedding vectors.
    Embeddings are stored as JSON arrays in MySQL.
    """

    def save_embedding(
        self,
        student_id: int,
        embedding: List[float],
        model_used: str = 'ArcFace'
    ) -> Tuple[bool, str, Optional[int]]:
        """
        Save a face embedding for a student.
        Replaces existing embedding if student already has one.

        Returns: (success, message, embedding_id)
        """
        from models.db import db, FaceEmbeddingModel
        try:
            # Delete existing embeddings for this student
            existing = FaceEmbeddingModel.query.filter_by(
                student_id=student_id
            ).first()

            if existing:
                existing.embedding_vector = embedding
                existing.model_used = model_used
                db.session.commit()
                logger.info(f"Updated embedding for student {student_id}")
                return True, "Embedding berhasil diperbarui.", existing.id

            # Create new embedding
            new_emb = FaceEmbeddingModel(
                student_id=student_id,
                embedding_vector=embedding,
                model_used=model_used
            )
            db.session.add(new_emb)
            db.session.commit()
            logger.info(f"Saved new embedding for student {student_id}, id={new_emb.id}")
            return True, "Embedding berhasil disimpan.", new_emb.id

        except Exception as e:
            db.session.rollback()
            logger.error(f"Failed to save embedding: {e}", exc_info=True)
            return False, f"Gagal menyimpan embedding: {str(e)}", None

    def load_all_embeddings(self) -> List[Tuple[int, List[float]]]:
        """
        Load all face embeddings from database.

        Returns: list of (student_id, embedding_vector)
        """
        from models.db import FaceEmbeddingModel
        try:
            records = FaceEmbeddingModel.query.all()
            result = []
            for rec in records:
                vec = rec.embedding_vector
                if isinstance(vec, str):
                    vec = json.loads(vec)
                result.append((rec.student_id, vec))
            logger.info(f"Loaded {len(result)} embeddings from database")
            return result
        except Exception as e:
            logger.error(f"Failed to load embeddings: {e}", exc_info=True)
            return []

    def delete_embedding(self, student_id: int) -> Tuple[bool, str]:
        """Delete face embedding for a student."""
        from models.db import db, FaceEmbeddingModel
        try:
            deleted = FaceEmbeddingModel.query.filter_by(
                student_id=student_id
            ).delete()
            db.session.commit()
            if deleted:
                return True, "Embedding berhasil dihapus."
            return False, "Embedding tidak ditemukan."
        except Exception as e:
            db.session.rollback()
            logger.error(f"Failed to delete embedding: {e}", exc_info=True)
            return False, str(e)

    @staticmethod
    def cosine_similarity(vec_a: List[float], vec_b: List[float]) -> float:
        """Compute cosine similarity between two vectors."""
        a = np.array(vec_a, dtype=np.float64)
        b = np.array(vec_b, dtype=np.float64)
        dot = np.dot(a, b)
        norm_a = np.linalg.norm(a)
        norm_b = np.linalg.norm(b)
        if norm_a == 0 or norm_b == 0:
            return 0.0
        return float(dot / (norm_a * norm_b))
