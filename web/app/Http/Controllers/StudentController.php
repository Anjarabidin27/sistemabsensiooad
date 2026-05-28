<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    /** Display a listing of students with search and filter. */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $prodi = $request->input('program_study');
        $status = $request->input('status');

        $query = Student::withCount('faceEmbeddings');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('student_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($prodi) {
            $query->where('program_study', $prodi);
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', (bool) $status);
        }

        $students = $query->orderBy('student_number', 'asc')->paginate(10)->withQueryString();
        
        // Get unique study programs for filter dropdown
        $programs = Student::select('program_study')
            ->whereNotNull('program_study')
            ->groupBy('program_study')
            ->pluck('program_study')
            ->toArray();

        return view('admin.students.index', compact('students', 'programs'));
    }

    /** Show the form for creating a new student. */
    public function create()
    {
        return view('admin.students.create');
    }

    /** Store a newly created student in storage. */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_number' => 'required|string|unique:students,student_number|max:20',
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|unique:students,email|max:100',
            'program_study' => 'nullable|string|max:100',
            'faculty' => 'nullable|string|max:100',
            'enrollment_year' => 'nullable|integer|min:2000|max:' . (date('Y') + 1),
            'password' => 'required|string|min:6',
            'is_active' => 'boolean',
            'photo' => 'nullable|image|max:2048', // max 2MB
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('students', 'public');
            $validated['photo_path'] = $path;
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        Student::create($validated);

        return redirect()->route('admin.students.index')->with('success', 'Mahasiswa berhasil ditambahkan.');
    }

    /** Show the form for editing the specified student. */
    public function edit(Student $student)
    {
        return view('admin.students.edit', compact('student'));
    }

    /** Update the specified student in storage. */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'student_number' => 'required|string|max:20|unique:students,student_number,' . $student->id,
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:100|unique:students,email,' . $student->id,
            'program_study' => 'nullable|string|max:100',
            'faculty' => 'nullable|string|max:100',
            'enrollment_year' => 'nullable|integer|min:2000|max:' . (date('Y') + 1),
            'password' => 'nullable|string|min:6',
            'is_active' => 'boolean',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($student->photo_path) {
                Storage::disk('public')->delete($student->photo_path);
            }
            $path = $request->file('photo')->store('students', 'public');
            $validated['photo_path'] = $path;
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : false;

        $student->update($validated);

        return redirect()->route('admin.students.index')->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    /** Remove the specified student from storage. */
    public function destroy(Student $student)
    {
        // Delete photo if exists
        if ($student->photo_path) {
            Storage::disk('public')->delete($student->photo_path);
        }

        $student->delete();

        return redirect()->route('admin.students.index')->with('success', 'Mahasiswa berhasil dihapus.');
    }
}
