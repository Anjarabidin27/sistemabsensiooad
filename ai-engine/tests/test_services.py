"""
SIHADIR — AI Engine Tests
Test face recognition pipeline.
"""
import json
import unittest
from unittest.mock import patch, MagicMock

import numpy as np


class TestLiveness(unittest.TestCase):

    def test_sharp_image_passes(self):
        """Sharp image should pass liveness check."""
        from services.liveness import LivenessService
        service = LivenessService(blur_threshold=50)
        # Create a sharp test image (high frequency content)
        img = np.random.randint(0, 255, (224, 224, 3), dtype=np.uint8)
        result = service.check(img)
        # Random noise has very high Laplacian variance
        self.assertTrue(result['is_live'])

    def test_uniform_image_fails(self):
        """Uniform/blank image should fail liveness."""
        from services.liveness import LivenessService
        service = LivenessService(blur_threshold=50)
        # All-white image = zero variance
        img = np.full((224, 224, 3), 200, dtype=np.uint8)
        result = service.check(img)
        self.assertFalse(result['is_live'])

    def test_liveness_returns_score(self):
        """Liveness result should always contain a score."""
        from services.liveness import LivenessService
        service = LivenessService(blur_threshold=50)
        img = np.zeros((224, 224, 3), dtype=np.uint8)
        result = service.check(img)
        self.assertIn('score', result)
        self.assertIn('is_live', result)
        self.assertIn('message', result)


class TestEmbeddingStoreSimilarity(unittest.TestCase):

    def test_identical_vectors(self):
        """Identical vectors should have similarity 1.0."""
        from services.embedding_store import EmbeddingStoreService
        vec = [0.1, 0.5, 0.3, 0.8]
        sim = EmbeddingStoreService.cosine_similarity(vec, vec)
        self.assertAlmostEqual(sim, 1.0, places=5)

    def test_orthogonal_vectors(self):
        """Orthogonal vectors should have similarity 0.0."""
        from services.embedding_store import EmbeddingStoreService
        a = [1.0, 0.0]
        b = [0.0, 1.0]
        sim = EmbeddingStoreService.cosine_similarity(a, b)
        self.assertAlmostEqual(sim, 0.0, places=5)

    def test_opposite_vectors(self):
        """Opposite vectors should have similarity -1.0."""
        from services.embedding_store import EmbeddingStoreService
        a = [1.0, 0.0]
        b = [-1.0, 0.0]
        sim = EmbeddingStoreService.cosine_similarity(a, b)
        self.assertAlmostEqual(sim, -1.0, places=5)


if __name__ == '__main__':
    unittest.main()
