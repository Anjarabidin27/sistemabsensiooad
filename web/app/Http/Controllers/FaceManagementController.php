<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\FaceEmbedding;
use App\Services\AiEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FaceManagementController extends Controller
{
    private AiEngineService $aiEngine;

    public function __construct(AiEngineService $aiEngine)
    {
        $this->aiEngine = $aiEngine;
    }

    /** Show the face registration page for a specific student. */
    public function show(Student $student)
    {
        $student->load('faceEmbeddings');
        return view('admin.students.face', compact('student'));
    }

    /** Handle face registration from webcam or file upload. */
    public function register(Request $request, Student $student)
    {
        $request->validate([
            'image' => 'nullable|image|max:5120', // if uploaded via file input
            'image_base64' => 'nullable|string', // if captured via webcam
        ]);

        $tempPath = null;
        $fileExtension = 'jpg';
        $fileContent = null;

        if ($request->filled('image_base64')) {
            // Decode webcam base64 image
            $imageData = $request->input('image_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]); // png, jpg, jpeg
                if (in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $fileExtension = $type;
                }
                $fileContent = base64_decode($imageData);
            }
        } elseif ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileExtension = $file->getClientOriginalExtension();
            $fileContent = file_get_contents($file->getRealPath());
        }

        if (!$fileContent) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak ada gambar yang dikirim atau format tidak didukung.'
            ], 400);
        }

        // Save image to temporary file in storage/app/temp
        $tempFileName = 'temp_' . $student->student_number . '_' . time() . '.' . $fileExtension;
        $tempPathLocal = 'temp/' . $tempFileName;
        Storage::disk('local')->put($tempPathLocal, $fileContent);
        $tempPath = storage_path('app/private/' . $tempPathLocal); // Laravel 11 private storage path is storage/app/private
        
        // Fallback for older laravel structure or custom path if private/ doesn't exist
        if (!file_exists($tempPath)) {
            $tempPath = storage_path('app/' . $tempPathLocal);
        }

        // Send to Python AI Engine
        $result = $this->aiEngine->registerFace($tempPath, $student->id, $student->name);

        // Clean up temp file
        Storage::disk('local')->delete($tempPathLocal);

        if (isset($result['status']) && $result['status'] === 'success') {
            // Save registered photo as official student profile photo
            $finalFolder = 'students';
            $finalFileName = $student->student_number . '_' . time() . '.' . $fileExtension;
            $finalPathLocal = $finalFolder . '/' . $finalFileName;
            
            Storage::disk('public')->put($finalPathLocal, $fileContent);

            // Update student photo path only if it is empty or is an old biometrics photo (preserving personal profile photo)
            if (!$student->photo_path || str_starts_with($student->photo_path, 'students/')) {
                if ($student->photo_path) {
                    Storage::disk('public')->delete($student->photo_path);
                }
                $student->update(['photo_path' => $finalPathLocal]);
            }

            // Update photo path in embedding record as well
            if (isset($result['embedding_id'])) {
                FaceEmbedding::where('id', $result['embedding_id'])->update([
                    'photo_path' => $finalPathLocal
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Wajah mahasiswa berhasil didaftarkan.',
                'photo_url' => asset('storage/' . $finalPathLocal)
            ]);
        }

        // Return error message from AI Engine
        $message = $result['message'] ?? 'Gagal mendaftarkan wajah.';
        return response()->json([
            'status' => 'error',
            'message' => $message
        ], 200); // return 200 to display custom error modal in frontend
    }

    /** Delete student's face embedding. */
    public function destroy(Student $student)
    {
        // Call Python AI Engine to delete
        $result = $this->aiEngine->deleteFace($student->id);

        if (isset($result['status']) && $result['status'] === 'success') {
            // Clear photo_path from student profile
            if ($student->photo_path) {
                Storage::disk('public')->delete($student->photo_path);
                $student->update(['photo_path' => null]);
            }

            return redirect()->route('admin.students.face', $student->id)
                ->with('success', 'Data wajah mahasiswa berhasil dihapus.');
        }

        return redirect()->route('admin.students.face', $student->id)
            ->with('error', 'Gagal menghapus data wajah: ' . ($result['message'] ?? 'Error tidak diketahui'));
    }
}
