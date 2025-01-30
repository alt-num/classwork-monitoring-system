<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            [
                'code' => 'BSIT',
                'name' => 'Bachelor of Science in Information Technology',
                'description' => 'A program that focuses on the study of information technology and its applications.',
            ],
            [
                'code' => 'BSCS',
                'name' => 'Bachelor of Science in Computer Science',
                'description' => 'A program that focuses on the theory and practice of computer science.',
            ],
            [
                'code' => 'BSCE',
                'name' => 'Bachelor of Science in Computer Engineering',
                'description' => 'A program that combines computer science and electronic engineering.',
            ],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}
