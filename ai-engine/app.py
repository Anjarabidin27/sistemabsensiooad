"""
SIHADIR — AI Engine Entry Point
Universitas Dian Nuswantoro

Flask application factory & route registration.
"""
import logging
import os
from logging.handlers import RotatingFileHandler

from flask import Flask
from flask_cors import CORS

from config import Config
from models.db import db
from routes.health import health_bp
from routes.recognize import recognize_bp
from routes.register import register_bp


def create_app():
    app = Flask(__name__)
    app.config.from_object(Config)

    # ── CORS ──────────────────────────────────────────────────
    CORS(app, resources={r"/api/*": {"origins": "*"}})

    # ── Database ───────────────────────────────────────────────
    db.init_app(app)
    with app.app_context():
        db.create_all()

    # ── Upload folder ──────────────────────────────────────────
    os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)
    os.makedirs('/app/logs', exist_ok=True)

    # ── Logging ────────────────────────────────────────────────
    _setup_logging(app)

    # ── Blueprints ─────────────────────────────────────────────
    app.register_blueprint(health_bp, url_prefix='/api')
    app.register_blueprint(recognize_bp, url_prefix='/api')
    app.register_blueprint(register_bp, url_prefix='/api')

    app.logger.info("SIHADIR AI Engine started successfully")
    return app


def _setup_logging(app: Flask):
    level = getattr(logging, app.config.get('LOG_LEVEL', 'INFO'), logging.INFO)
    formatter = logging.Formatter(
        '[%(asctime)s] %(levelname)s in %(module)s: %(message)s'
    )

    # File handler (rotating, max 10MB, keep 5 backups)
    file_handler = RotatingFileHandler(
        app.config['LOG_FILE'],
        maxBytes=10 * 1024 * 1024,
        backupCount=5
    )
    file_handler.setFormatter(formatter)
    file_handler.setLevel(level)

    # Console handler
    console_handler = logging.StreamHandler()
    console_handler.setFormatter(formatter)
    console_handler.setLevel(level)

    app.logger.handlers.clear()
    app.logger.addHandler(file_handler)
    app.logger.addHandler(console_handler)
    app.logger.setLevel(level)


app = create_app()

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=app.config['DEBUG'])
