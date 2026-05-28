"""
SIHADIR — Liveness Detection Service
Basic anti-spoofing using blur/Laplacian variance analysis.
Prevents printed photo / screen photo attacks.
"""
import logging

import cv2
import numpy as np

logger = logging.getLogger(__name__)


class LivenessService:
    """
    Passive liveness detection via image texture analysis.

    Method: Laplacian Variance
    - A real face has natural texture → high Laplacian variance.
    - A printed photo or screen: typically lower variance or different frequency.
    - Threshold is configurable.

    Note: This is a lightweight check (not DL-based).
    Adequate for MVP; can be replaced with a deep model later.
    """

    def __init__(self, blur_threshold: int = 50):
        """
        Args:
            blur_threshold: Minimum Laplacian variance to consider image non-blurry.
                            Lower = more permissive. Default: 50.
        """
        self.blur_threshold = blur_threshold

    def check(self, image: np.ndarray) -> dict:
        """
        Run liveness check on image.

        Returns:
            {
                'is_live': bool,
                'score': float,       # Laplacian variance
                'message': str
            }
        """
        try:
            gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY) \
                if len(image.shape) == 3 else image

            laplacian_var = cv2.Laplacian(gray, cv2.CV_64F).var()
            is_live = laplacian_var >= self.blur_threshold

            logger.debug(
                f"Liveness check: variance={laplacian_var:.2f}, "
                f"threshold={self.blur_threshold}, is_live={is_live}"
            )

            if is_live:
                return {
                    'is_live': True,
                    'score': round(laplacian_var, 2),
                    'message': 'Liveness check passed.'
                }
            else:
                return {
                    'is_live': False,
                    'score': round(laplacian_var, 2),
                    'message': (
                        f'Gambar terdeteksi terlalu buram atau kemungkinan foto cetak '
                        f'(skor: {laplacian_var:.1f}, minimum: {self.blur_threshold}). '
                        'Pastikan kamera fokus dan wajah tampak jelas.'
                    )
                }

        except Exception as e:
            logger.error(f"Liveness check error: {e}", exc_info=True)
            # Fail open — don't block on liveness error
            return {
                'is_live': True,
                'score': -1.0,
                'message': f'Liveness check error (skipped): {str(e)}'
            }
