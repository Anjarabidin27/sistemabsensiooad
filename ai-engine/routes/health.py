"""
SIHADIR — Health Check Route
GET /api/health
"""
import time

from flask import Blueprint, jsonify

health_bp = Blueprint('health', __name__)

_start_time = time.time()


@health_bp.route('/health', methods=['GET'])
def health():
    """Returns service status and uptime."""
    uptime = int(time.time() - _start_time)
    return jsonify({
        'status': 'ok',
        'service': 'SIHADIR AI Engine',
        'model': 'ArcFace',
        'uptime_seconds': uptime,
        'version': '1.0.0'
    }), 200
