<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /** Display a listing of courses. */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $day = $request->input('day');
        $semester = $request->input('semester');

        $query = Course::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('lecturer_name', 'like', "%{$search}%");
            });
        }

        if ($day !== null && $day !== '') {
            $query->where('schedule_day', (int) $day);
        }

        if ($semester) {
            $query->where('semester', $semester);
        }

        $courses = $query->orderBy('schedule_day', 'asc')
            ->orderBy('schedule_start', 'asc')
            ->paginate(10)
            ->withQueryString();

        // Get unique semesters for filter
        $semesters = Course::select('semester')
            ->whereNotNull('semester')
            ->groupBy('semester')
            ->pluck('semester')
            ->toArray();

        return view('admin.courses.index', compact('courses', 'semesters'));
    }

    /** Show the form for creating a new course. */
    public function create()
    {
        return view('admin.courses.create');
    }

    /** Store a newly created course in storage. */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'              => 'required|string|unique:courses,code|max:20',
            'name'              => 'required|string|max:100',
            'credits'           => 'required|integer|min:1|max:6',
            'lecturer_name'     => 'nullable|string|max:100',
            'schedule_day'      => 'required|integer|min:0|max:6',
            'schedule_start'    => 'required|date_format:H:i',
            'schedule_end'      => 'required|date_format:H:i|after:schedule_start',
            'room'              => 'nullable|string|max:50',
            'semester'          => 'required|string|max:20',
            'is_active'         => 'boolean',
            'latitude'          => 'nullable|numeric|between:-90,90',
            'longitude'         => 'nullable|numeric|between:-180,180',
            'location_radius'   => 'nullable|integer|min:10|max:2000',
            'location_required' => 'boolean',
        ]);

        $validated['is_active']         = $request->has('is_active') ? $request->boolean('is_active') : true;
        $validated['location_required'] = $request->has('location_required') ? $request->boolean('location_required') : false;
        $validated['location_radius']   = $validated['location_radius'] ?? 100;

        Course::create($validated);

        return redirect()->route('admin.courses.index')->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    /** Show the form for editing the specified course. */
    public function edit(Course $course)
    {
        return view('admin.courses.edit', compact('course'));
    }

    /** Update the specified course in storage. */
    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'code'              => 'required|string|max:20|unique:courses,code,' . $course->id,
            'name'              => 'required|string|max:100',
            'credits'           => 'required|integer|min:1|max:6',
            'lecturer_name'     => 'nullable|string|max:100',
            'schedule_day'      => 'required|integer|min:0|max:6',
            'schedule_start'    => 'required|date_format:H:i',
            'schedule_end'      => 'required|date_format:H:i|after:schedule_start',
            'room'              => 'nullable|string|max:50',
            'semester'          => 'required|string|max:20',
            'is_active'         => 'boolean',
            'latitude'          => 'nullable|numeric|between:-90,90',
            'longitude'         => 'nullable|numeric|between:-180,180',
            'location_radius'   => 'nullable|integer|min:10|max:2000',
            'location_required' => 'boolean',
        ]);

        $validated['is_active']         = $request->has('is_active') ? $request->boolean('is_active') : false;
        $validated['location_required'] = $request->has('location_required') ? $request->boolean('location_required') : false;
        $validated['location_radius']   = $validated['location_radius'] ?? 100;

        $course->update($validated);

        return redirect()->route('admin.courses.index')->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    /** Remove the specified course from storage. */
    public function destroy(Course $course)
    {
        $course->delete();
        return redirect()->route('admin.courses.index')->with('success', 'Mata kuliah berhasil dihapus.');
    }

    /** Toggle location_required on/off directly from the table. */
    public function toggleLocation(Course $course)
    {
        $course->update(['location_required' => !$course->location_required]);

        $status = $course->location_required ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Deteksi lokasi berhasil {$status} untuk mata kuliah {$course->name}.");
    }
}
