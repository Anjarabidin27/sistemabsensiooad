"""
SIHADIR — Image Utilities
Helper functions for image handling.
"""
import hashlib
import logging
import os
import uuid
from typing import Optional

import cv2
import numpy as np
from flask import current_app

logger = logging.getLogger(__name__)


def allowed_file(filename: str) -> bool:
    """Check if file extension is allowed."""
    allowed = current_app.config.get('ALLOWED_EXTENSIONS', {'png', 'jpg', 'jpeg', 'webp'})
    return '.' in filename and filename.rsplit('.', 1)[1].lower() in allowed


def save_upload(file_bytes: bytes, prefix: str = 'upload') -> Optional[str]:
    """
    Save raw bytes to upload folder with unique filename.
    Returns relative path or None on failure.
    """
    try:
        upload_folder = current_app.config['UPLOAD_FOLDER']
        filename = f"{prefix}_{uuid.uuid4().hex}.jpg"
        filepath = os.path.join(upload_folder, filename)
        with open(filepath, 'wb') as f:
            f.write(file_bytes)
        return filename
    except Exception as e:
        logger.error(f"Failed to save upload: {e}")
        return None


def compute_hash(data: bytes) -> str:
    """Compute SHA-256 hash of image bytes."""
    return hashlib.sha256(data).hexdigest()


def resize_image(image: np.ndarray, max_size: int = 640) -> np.ndarray:
    """Resize image keeping aspect ratio if larger than max_size."""
    h, w = image.shape[:2]
    if max(h, w) <= max_size:
        return image
    scale = max_size / max(h, w)
    new_w, new_h = int(w * scale), int(h * scale)
    return cv2.resize(image, (new_w, new_h), interpolation=cv2.INTER_AREA)
