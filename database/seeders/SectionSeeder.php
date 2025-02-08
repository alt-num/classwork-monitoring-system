<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::all();
        $sections = ['A', 'B', 'C', 'D', 'E', 'F'];
        $yearLevels = [
            User::YEAR_FIRST,
            User::YEAR_SECOND,
            User::YEAR_THIRD,
            User::YEAR_FOURTH
        ];

        foreach ($courses as $course) {
            foreach ($yearLevels as $yearLevel) {
                foreach ($sections as $sectionName) {
                    Section::create([
                        'name' => $sectionName,
                        'year_level' => $yearLevel,
                        'course_id' => $course->id,
                    ]);
                }
            }
        }
    }
}
