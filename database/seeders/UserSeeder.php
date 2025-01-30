<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@cms.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Get all courses and sections
        $courses = Course::all();
        $sections = Section::all();

        // Create one secretary per course
        foreach ($courses as $course) {
            User::create([
                'name' => "Secretary {$course->name}",
                'username' => "secretary_{$course->code}",
                'email' => "secretary_{$course->code}@cms.test",
                'password' => Hash::make('password'),
                'role' => 'secretary',
                'contact_number' => '09' . rand(100000000, 999999999),
                'course_id' => $course->id,
                'section_id' => $sections->where('course_id', $course->id)->first()->id,
            ]);
        }

        // Create 5 students per section
        foreach ($sections as $section) {
            for ($i = 1; $i <= 5; $i++) {
                $studentId = date('Y') . str_pad($section->course_id, 2, '0', STR_PAD_LEFT) 
                    . str_pad($section->id, 2, '0', STR_PAD_LEFT) 
                    . str_pad($i, 3, '0', STR_PAD_LEFT);

                User::create([
                    'name' => "Student {$studentId}",
                    'username' => $studentId,
                    'email' => "{$studentId}@student.cms.test",
                    'password' => Hash::make('password'),
                    'role' => 'student',
                    'student_id' => $studentId,
                    'contact_number' => '09' . rand(100000000, 999999999),
                    'course_id' => $section->course_id,
                    'section_id' => $section->id,
                ]);
            }
        }
    }
}
