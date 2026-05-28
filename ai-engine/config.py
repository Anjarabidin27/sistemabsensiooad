"""
SIHADIR — AI Engine Configuration
Universitas Dian Nuswantoro
"""
import os
from dotenv import load_dotenv

load_dotenv()


class Config:
    # Flask
    DEBUG = os.getenv('FLASK_ENV', 'production') == 'development'
    SECRET_KEY = os.getenv('SECRET_KEY', 'sihadir-secret-2024')

    # Database
    DB_HOST = os.getenv('DB_HOST', 'localhost')
    DB_PORT = int(os.getenv('DB_PORT', 3306))
    DB_DATABASE = os.getenv('DB_DATABASE', 'sihadir_db')
    DB_USERNAME = os.getenv('DB_USERNAME', 'sihadir_user')
    DB_PASSWORD = os.getenv('DB_PASSWORD', 'sihadir_pass')

    SQLALCHEMY_DATABASE_URI = (
        f"mysql+pymysql://{DB_USERNAME}:{DB_PASSWORD}"
        f"@{DB_HOST}:{DB_PORT}/{DB_DATABASE}?charset=utf8mb4"
    )
    SQLALCHEMY_TRACK_MODIFICATIONS = False

    # File Upload
    UPLOAD_FOLDER = os.getenv('UPLOAD_FOLDER', '/app/uploads')
    MAX_CONTENT_LENGTH = 10 * 1024 * 1024  # 10 MB
    ALLOWED_EXTENSIONS = {'png', 'jpg', 'jpeg', 'webp'}

    # Face Recognition
    FACE_MODEL = os.getenv('FACE_MODEL', 'ArcFace')
    FACE_DETECTOR = os.getenv('FACE_DETECTOR', 'opencv')
    CONFIDENCE_THRESHOLD = float(os.getenv('CONFIDENCE_THRESHOLD', 0.80))

    # Liveness Detection
    LIVENESS_BLUR_THRESHOLD = int(os.getenv('LIVENESS_BLUR_THRESHOLD', 50))

    # Logging
    LOG_LEVEL = os.getenv('LOG_LEVEL', 'INFO')
    LOG_FILE = '/app/logs/ai_engine.log'
