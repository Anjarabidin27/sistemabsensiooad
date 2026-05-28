<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Student;
use App\Models\RecognitionLog;
use App\Services\AiEngineService;
use App\Services\SettingsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    private AiEngineService $aiEngine;
    private SettingsService $settings;

    public function __construct(AiEngineService $aiEngine, SettingsService $settings)
    {
        $this->aiEngine = $aiEngine;
        $this->settings = $settings;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STUDENT ACTIONS
    // ─────────────────────────────────────────────────────────────────────────

    /** Student Home / Beranda. */
    public function studentHome()
    {
        $student = Auth::guard('student')->user();
        $student->loadCount('faceEmbeddings');

        // Stats for current student
        $attendances = Attendance::where('student_id', $student->id)->get();
        $totalClasses = $attendances->count();
        $present = $attendances->where('status', 'present')->count();
        $late = $attendances->where('status', 'late')->count();
        
        $attendanceRate = $totalClasses > 0 ? (($present + $late) / $totalClasses) * 100 : 0;

        // Current schedule today
        $todayDay = Carbon::now()->dayOfWeekIso - 1; // 0=Senin, 6=Minggu
        $coursesToday = Course::where('schedule_day', $todayDay)
            ->where('is_active', true)
            ->orderBy('schedule_start', 'asc')
            ->get();

        // Latest check-in today
        $latestCheckinToday = Attendance::with('course')
            ->where('student_id', $student->id)
            ->whereDate('check_in_at', Carbon::today())
            ->orderBy('check_in_at', 'desc')
            ->first();

        return view('student.home', compact('student', 'present', 'late', 'attendanceRate', 'coursesToday', 'latestCheckinToday'));
    }

    /** Show webcam face scanner for student. */
    public function showScanner()
    {
        $student = Auth::guard('student')->user();
        
        // Ensure student has registered a face first!
        if (!$student->hasFaceRegistered()) {
            return redirect()->route('student.home')
                ->with('error', 'Wajah Anda belum terdaftar di sistem. Hubungi administrator untuk mendaftarkan wajah.');
        }

        // Get active courses strictly for today only
        $todayDay = Carbon::now()->dayOfWeekIso - 1; // 0 = Monday, 6 = Sunday
        $courses = Course::where('is_active', true)
            ->where('schedule_day', $todayDay)
            ->orderBy('schedule_start', 'asc')
            ->get();

        return view('student.scanner', compact('courses'));
    }

    /** Process face scan. */
    public function processScan(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'image_base64' => 'required|string',
        ]);

        $student = Auth::guard('student')->user();
        $course = Course::findOrFail($request->input('course_id'));

        // 1. Process base64 image data
        $imageData = $request->input('image_base64');
        if (!preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
            return response()->json(['status' => 'error', 'message' => 'Format gambar tidak valid.'], 400);
        }

        $imageData = substr($imageData, strpos($imageData, ',') + 1);
        $fileContent = base64_decode($imageData);
        $fileExtension = strtolower($type[1]) ?: 'jpg';

        // 2. Save temporary file for Python AI Engine
        $tempFileName = 'scan_' . $student->student_number . '_' . time() . '.' . $fileExtension;
        $tempPathLocal = 'temp/' . $tempFileName;
        Storage::disk('local')->put($tempPathLocal, $fileContent);
        $tempPath = storage_path('app/private/' . $tempPathLocal);
        if (!file_exists($tempPath)) {
            $tempPath = storage_path('app/' . $tempPathLocal);
        }

        // 3. Call AI engine to recognize
        $startTime = time();
        $result = $this->aiEngine->recognize($tempPath, session()->getId());
        $processingTimeMs = (int) ((time() - $startTime) * 1000);

        // Delete temp scan file from private storage
        Storage::disk('local')->delete($tempPathLocal);

        $threshold = $this->settings->confidenceThreshold();

        // 4. Log AI recognition attempt
        $recognizedStudentId = null;
        $confidence = $result['confidence'] ?? 0.0;
        $logResult = 'unknown';

        if (isset($result['status']) && $result['status'] === 'recognized') {
            $recognizedStudentId = (int) $result['student_id'];
            
            // Check if matching student is indeed the logged-in student!
            if ($recognizedStudentId !== $student->id) {
                // Mismatch: face matches another student!
                $logResult = 'rejected';
                
                // Log attempt
                RecognitionLog::create([
                    'student_id' => $recognizedStudentId,
                    'image_hash' => hash('sha256', $fileContent),
                    'result' => 'spoofing', // flag as mismatch/spoofing
                    'confidence_score' => $confidence,
                    'processing_time_ms' => $processingTimeMs,
                    'error_message' => "Wajah terdeteksi sebagai mahasiswa ID {$recognizedStudentId}, bukan mahasiswa yang sedang login ({$student->name})."
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Scan gagal. Wajah Anda terdeteksi milik mahasiswa lain! Harap scan wajah Anda sendiri.'
                ]);
            }

            if ($confidence >= $threshold) {
                $logResult = 'recognized';
            }
        } elseif (isset($result['status']) && $result['status'] === 'spoofing') {
            $logResult = 'spoofing';
        } elseif (isset($result['status']) && $result['status'] === 'no_face') {
            $logResult = 'no_face';
        }

        // Save log to DB
        RecognitionLog::create([
            'student_id' => $recognizedStudentId ?: $student->id,
            'image_hash' => hash('sha256', $fileContent),
            'result' => $logResult,
            'confidence_score' => $confidence,
            'processing_time_ms' => $processingTimeMs,
            'error_message' => $result['message'] ?? null
        ]);

        if ($logResult !== 'recognized') {
            $message = 'Wajah tidak dikenali atau di bawah threshold akurasi. Silakan coba lagi.';
            if ($logResult === 'spoofing') $message = 'Deteksi anti-spoofing gagal. Harap gunakan wajah asli Anda di depan kamera.';
            if ($logResult === 'no_face') $message = 'Wajah tidak terdeteksi di kamera. Posisikan wajah Anda di tengah bingkai.';
            
            return response()->json([
                'status' => 'error',
                'message' => $message,
                'confidence' => $confidence
            ]);
        }

        // 5. Check if already checked in today for this course
        $alreadyCheckedIn = Attendance::where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->whereDate('check_in_at', Carbon::today())
            ->exists();

        if ($alreadyCheckedIn) {
            return response()->json([
                'status' => 'info',
                'message' => 'Anda sudah melakukan presensi hari ini untuk mata kuliah: ' . $course->name
            ]);
        }

        // 6. Save permanent check-in photo
        $finalFolder = 'attendances';
        $finalFileName = $student->student_number . '_' . $course->code . '_' . time() . '.' . $fileExtension;
        $finalPathLocal = $finalFolder . '/' . $finalFileName;
        Storage::disk('public')->put($finalPathLocal, $fileContent);

        // 7. Calculate status: present vs late
        $checkInAt = Carbon::now();
        $status = 'present';

        // Check course schedule for today
        $todayDay = $checkInAt->dayOfWeekIso - 1;
        if ($course->schedule_day == $todayDay && $course->schedule_start) {
            $scheduleStart = Carbon::createFromFormat('H:i:s', $course->schedule_start);
            $scheduleStart->setDate($checkInAt->year, $checkInAt->month, $checkInAt->day);
            
            $lateLimitMinutes = $this->settings->lateThresholdMinutes();
            $lateThresholdTime = $scheduleStart->copy()->addMinutes($lateLimitMinutes);

            if ($checkInAt->greaterThan($lateThresholdTime)) {
                $status = 'late';
            }
        }

        // 8. Create attendance record
        Attendance::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'check_in_at' => $checkInAt,
            'confidence_score' => $confidence,
            'status' => $status,
            'image_path' => $finalPathLocal,
            'ip_address' => $request->ip(),
            'notes' => 'Presensi sukses via Face Recognition'
        ]);

        // 9. Construct personalized modal message
        $lateLimitMinutes = $this->settings->lateThresholdMinutes();
        if ($status === 'present') {
            $popupMessage = "Halo <strong>{$student->name}</strong> ({$student->student_number}), absen Anda berhasil tepat waktu! Selamat belajar!";
        } else {
            $popupMessage = "Halo <strong>{$student->name}</strong> ({$student->student_number}), absen Anda berhasil nih namun sayang banget Anda terlambat. Lain kali jangan sampai terlambat ya, batas toleransi terlambat adalah <strong>{$lateLimitMinutes} menit</strong>. Selamat belajar!";
        }

        return response()->json([
            'status' => 'success',
            'attendance_status' => $status, // 'present' or 'late'
            'message' => 'Absen berhasil!',
            'popup_message' => $popupMessage,
            'student_name' => $student->name,
            'check_in_time' => $checkInAt->format('H:i:s')
        ]);
    }

    /** Student History. */
    public function studentHistory(Request $request)
    {
        $student = Auth::guard('student')->user();

        $courseId = $request->input('course_id');
        $date = $request->input('date');
        $status = $request->input('status');

        $query = Attendance::with('course')
            ->where('student_id', $student->id);

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        if ($date) {
            $query->whereDate('check_in_at', $date);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $attendances = $query->orderBy('check_in_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Calculate student stats
        $allStudentAttendances = Attendance::where('student_id', $student->id)->get();
        $totalClasses = $allStudentAttendances->count();
        $present = $allStudentAttendances->where('status', 'present')->count();
        $late = $allStudentAttendances->where('status', 'late')->count();
        $attendanceRate = $totalClasses > 0 ? (($present + $late) / $totalClasses) * 100 : 0;

        // Fetch active courses for the dropdown filter
        $courses = Course::where('is_active', true)->orderBy('name', 'asc')->get();

        return view('student.history', compact(
            'attendances',
            'courses',
            'present',
            'late',
            'totalClasses',
            'attendanceRate'
        ));
    }

    /** Student Schedule. */
    public function studentSchedule()
    {
        $courses = Course::where('is_active', true)
            ->orderBy('schedule_day', 'asc')
            ->orderBy('schedule_start', 'asc')
            ->get();

        return view('student.schedule', compact('courses'));
    }

    /** Student Profile. */
    public function studentProfile()
    {
        $student = Auth::guard('student')->user();
        return view('student.profile', compact('student'));
    }

    /** Upload Student Profile Photo. */
    public function uploadProfilePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048', // max 2MB
        ]);

        $student = Auth::guard('student')->user();

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            
            $fileExtension = $file->getClientOriginalExtension();
            $fileName = 'profile_' . $student->student_number . '_' . time() . '.' . $fileExtension;
            $pathLocal = 'profiles/' . $fileName;

            // Ensure directory exists
            Storage::disk('public')->makeDirectory('profiles');

            // Save to public storage
            Storage::disk('public')->put($pathLocal, file_get_contents($file->getRealPath()));

            // Delete old profile photo from disk if it was in profiles/
            if ($student->photo_path && str_starts_with($student->photo_path, 'profiles/')) {
                Storage::disk('public')->delete($student->photo_path);
            }

            // Update student photo_path
            $student->update(['photo_path' => $pathLocal]);

            return redirect()->route('student.profile')->with('success', 'Foto profil berhasil diperbarui.');
        }

        return redirect()->route('student.profile')->with('error', 'Gagal memperbarui foto profil.');
    }


    // ─────────────────────────────────────────────────────────────────────────
    // ADMIN ACTIONS
    // ─────────────────────────────────────────────────────────────────────────

    /** Admin: View Attendances Log. */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $courseId = $request->input('course_id');
        $status = $request->input('status');
        $date = $request->input('date');

        $query = Attendance::with(['student', 'course']);

        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('student_number', 'like', "%{$search}%");
            });
        }

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($date) {
            $query->whereDate('check_in_at', $date);
        }

        $attendances = $query->orderBy('check_in_at', 'desc')->paginate(15)->withQueryString();
        
        $courses = Course::where('is_active', true)->get();

        return view('admin.attendances.index', compact('attendances', 'courses'));
    }

    /** Admin: Delete Attendance Record. */
    public function destroy(Attendance $attendance)
    {
        // Delete image file if exists
        if ($attendance->image_path) {
            Storage::disk('public')->delete($attendance->image_path);
        }

        $attendance->delete();

        return redirect()->route('admin.attendances.index')->with('success', 'Catatan presensi berhasil dihapus.');
    }
}
