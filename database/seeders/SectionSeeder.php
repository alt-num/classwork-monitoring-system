<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Section;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::all();

        foreach ($courses as $course) {
            // Create sections for each year level (1-4)
            for ($year = 1; $year <= 4; $year++) {
                // Create sections A, B, C for each year level
                foreach (['A', 'B', 'C'] as $sectionName) {
                    Section::create([
                        'name' => $sectionName,
                        'year_level' => $year,
                        'course_id' => $course->id,
                    ]);
                }
            }
        }
    }
}
