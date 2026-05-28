<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /** Display reports page. */
    public function index(Request $request)
    {
        $courses = Course::where('is_active', true)->get();
        $selectedCourseId = $request->input('course_id');
        $dateStart = $request->input('date_start', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateEnd = $request->input('date_end', Carbon::now()->format('Y-m-d'));

        // Attendance stats per course
        $courseStats = [];
        foreach ($courses as $course) {
            $attendances = Attendance::where('course_id', $course->id)
                ->whereBetween('check_in_at', [
                    Carbon::parse($dateStart)->startOfDay(),
                    Carbon::parse($dateEnd)->endOfDay()
                ])->get();

            $present = $attendances->where('status', 'present')->count();
            $late = $attendances->where('status', 'late')->count();
            $rejected = $attendances->where('status', 'rejected')->count();
            $total = $attendances->count();

            $courseStats[] = [
                'course' => $course,
                'present' => $present,
                'late' => $late,
                'rejected' => $rejected,
                'total' => $total
            ];
        }

        // Detailed student check-in summary if course is selected
        $detailedReport = [];
        if ($selectedCourseId) {
            $selectedCourse = Course::findOrFail($selectedCourseId);
            
            // Get all students
            $students = Student::where('is_active', true)->orderBy('student_number', 'asc')->get();
            
            foreach ($students as $student) {
                $studentAttendances = Attendance::where('student_id', $student->id)
                    ->where('course_id', $selectedCourseId)
                    ->whereBetween('check_in_at', [
                        Carbon::parse($dateStart)->startOfDay(),
                        Carbon::parse($dateEnd)->endOfDay()
                    ])->get();

                $presentCount = $studentAttendances->where('status', 'present')->count();
                $lateCount = $studentAttendances->where('status', 'late')->count();
                $rejectedCount = $studentAttendances->where('status', 'rejected')->count();

                $detailedReport[] = [
                    'student' => $student,
                    'present' => $presentCount,
                    'late' => $lateCount,
                    'rejected' => $rejectedCount,
                    'total' => $studentAttendances->count()
                ];
            }
        }

        return view('admin.reports', compact('courses', 'selectedCourseId', 'dateStart', 'dateEnd', 'courseStats', 'detailedReport'));
    }

    /** Export attendance records to CSV. */
    public function export(Request $request)
    {
        $courseId = $request->input('course_id');
        $status = $request->input('status');
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');

        $query = Attendance::with(['student', 'course']);

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($dateStart && $dateEnd) {
            $query->whereBetween('check_in_at', [
                Carbon::parse($dateStart)->startOfDay(),
                Carbon::parse($dateEnd)->endOfDay()
            ]);
        }

        $attendances = $query->orderBy('check_in_at', 'asc')->get();

        $fileName = 'rekap_presensi_' . time() . '.csv';

        $response = new StreamedResponse(function () use ($attendances) {
            $handle = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel support
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Headers
            fputcsv($handle, [
                'No', 
                'NIM', 
                'Nama Mahasiswa', 
                'Mata Kuliah', 
                'Kode MK', 
                'Tanggal', 
                'Waktu Presensi', 
                'Status', 
                'Confidence Score', 
                'IP Address'
            ]);

            $i = 1;
            foreach ($attendances as $row) {
                fputcsv($handle, [
                    $i++,
                    $row->student->student_number,
                    $row->student->name,
                    $row->course ? $row->course->name : '-',
                    $row->course ? $row->course->code : '-',
                    $row->check_in_at->format('Y-m-d'),
                    $row->check_in_at->format('H:i:s'),
                    $row->status === 'present' ? 'Hadir' : ($row->status === 'late' ? 'Terlambat' : 'Ditolak'),
                    number_format($row->confidence_score * 100, 1) . '%',
                    $row->ip_address
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);

        return $response;
    }
}
