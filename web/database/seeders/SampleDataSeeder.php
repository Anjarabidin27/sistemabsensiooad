<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Support\Facades\Hash;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── Sample Courses ──────────────────────────────────
        $courses = [
            [
                'code'           => 'A11.3503',
                'name'           => 'Object Oriented Analysis and Design',
                'credits'        => 3,
                'lecturer_name'  => 'Dr. Edy Mulyanto, S.Si, M.Kom',
                'schedule_day'   => 2, // Rabu
                'schedule_start' => '08:00:00',
                'schedule_end'   => '10:30:00',
                'room'           => 'Lab Komputer 2',
                'semester'       => '2025/2026-Genap',
                'is_active'      => true,
            ],
            [
                'code'           => 'A11.2501',
                'name'           => 'Pemrograman Web',
                'credits'        => 3,
                'lecturer_name'  => 'Dr. Heru Agus Santoso, Ph.D',
                'schedule_day'   => 0, // Senin
                'schedule_start' => '13:00:00',
                'schedule_end'   => '15:30:00',
                'room'           => 'Lab Multimedia',
                'semester'       => '2025/2026-Genap',
                'is_active'      => true,
            ],
            [
                'code'           => 'A11.2601',
                'name'           => 'Basis Data Lanjut',
                'credits'        => 3,
                'lecturer_name'  => 'Wellia Shinta Sari, M.Kom',
                'schedule_day'   => 3, // Kamis
                'schedule_start' => '10:00:00',
                'schedule_end'   => '12:30:00',
                'room'           => 'RK 301',
                'semester'       => '2025/2026-Genap',
                'is_active'      => true,
            ],
        ];

        foreach ($courses as $course) {
            Course::updateOrCreate(['code' => $course['code']], $course);
        }

        // ── Sample Students ─────────────────────────────────
        $students = [
            [
                'student_number' => 'A11.2023.15023',
                'name'           => 'Nugroho Anjar Abidin',
                'email'          => 'nugroho@mhs.dinus.ac.id',
                'program_study'  => 'Teknik Informatika',
                'faculty'        => 'Ilmu Komputer',
                'enrollment_year'=> 2023,
                'password'       => Hash::make('password'),
                'is_active'      => true,
            ],
            [
                'student_number' => 'A11.2023.15024',
                'name'           => 'Budi Santoso',
                'email'          => 'budi@mhs.dinus.ac.id',
                'program_study'  => 'Teknik Informatika',
                'faculty'        => 'Ilmu Komputer',
                'enrollment_year'=> 2023,
                'password'       => Hash::make('password'),
                'is_active'      => true,
            ],
            [
                'student_number' => 'A11.2023.15025',
                'name'           => 'Siti Rahayu',
                'email'          => 'siti@mhs.dinus.ac.id',
                'program_study'  => 'Teknik Informatika',
                'faculty'        => 'Ilmu Komputer',
                'enrollment_year'=> 2023,
                'password'       => Hash::make('password'),
                'is_active'      => true,
            ],
        ];

        foreach ($students as $student) {
            Student::updateOrCreate(['student_number' => $student['student_number']], $student);
        }

        $this->command->info('Sample data seeded: ' . count($courses) . ' courses, ' . count($students) . ' students.');
    }
}
