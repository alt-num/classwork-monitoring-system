<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseManagementSeeder extends Seeder
{
    public function run(): void
    {
        // Begin transaction for safety
        DB::beginTransaction();
        
        try {
            // Example of how to remove a course safely
            $courseCode = $this->command->ask('Enter course code to remove (or press enter to skip)');
            
            if ($courseCode) {
                $course = Course::where('code', $courseCode)->first();
                if ($course) {
                    $usersCount = $course->users()->count();
                    if ($usersCount > 0) {
                        $this->command->error("Cannot delete {$courseCode} - It has {$usersCount} users associated with it.");
                    } else {
                        // Delete all sections first
                        $course->sections()->delete();
                        $this->command->info("Removed all {$courseCode} sections.");
                        
                        // Then delete the course
                        $course->delete();
                        $this->command->info("Successfully removed {$courseCode} course.");
                    }
                } else {
                    $this->command->info("Course {$courseCode} not found.");
                }
            }

            // Example of how to add a new course
            if ($this->command->confirm('Would you like to add a new course?', false)) {
                $code = $this->command->ask('Enter course code (e.g., BSIT)');
                $name = $this->command->ask('Enter course name');
                $description = $this->command->ask('Enter course description');

                $course = Course::firstOrCreate(
                    ['code' => $code],
                    [
                        'name' => $name,
                        'description' => $description
                    ]
                );

                if ($course->wasRecentlyCreated) {
                    $this->command->info("Added {$code} course successfully.");
                } else {
                    $this->command->info("Course {$code} already exists.");
                }
            }

            DB::commit();
            $this->command->info('Course management completed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
} 