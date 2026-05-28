"""
SIHADIR — SQLAlchemy Database Models (AI Engine)
"""
from datetime import datetime

from flask_sqlalchemy import SQLAlchemy

db = SQLAlchemy()


class FaceEmbeddingModel(db.Model):
    """Stores face embeddings extracted by AI engine."""
    __tablename__ = 'face_embeddings'

    id = db.Column(db.BigInteger, primary_key=True, autoincrement=True)
    student_id = db.Column(db.BigInteger, nullable=False, index=True)
    embedding_vector = db.Column(db.JSON, nullable=False)
    model_used = db.Column(db.String(50), nullable=False, default='ArcFace')
    created_at = db.Column(db.DateTime, default=datetime.now)

    def to_dict(self):
        return {
            'id': self.id,
            'student_id': self.student_id,
            'model_used': self.model_used,
            'created_at': self.created_at.isoformat() if self.created_at else None,
        }


class RecognitionLogModel(db.Model):
    """Audit log for every recognition attempt."""
    __tablename__ = 'recognition_logs'

    id = db.Column(db.BigInteger, primary_key=True, autoincrement=True)
    student_id = db.Column(db.BigInteger, nullable=True)  # NULL if unknown
    image_hash = db.Column(db.String(64), nullable=True)
    result = db.Column(
        db.Enum('recognized', 'unknown', 'error', 'spoofing', 'no_face'),
        nullable=False
    )
    confidence_score = db.Column(db.Numeric(5, 4), nullable=True)
    processing_time_ms = db.Column(db.Integer, nullable=True)
    error_message = db.Column(db.Text, nullable=True)
    created_at = db.Column(db.DateTime, default=datetime.now)
