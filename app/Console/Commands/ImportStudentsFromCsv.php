<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Section;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use App\Models\Fine;

class ImportStudentsFromCsv extends Command
{
    protected $signature = 'students:import {file : Path to the CSV file} {course_code : Course code for the students}';
    protected $description = 'Import students from a CSV file';

    public function handle()
    {
        $file = $this->argument('file');
        $courseCode = $this->argument('course_code');

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        // Verify course exists
        $course = Course::where('code', $courseCode)->first();
        if (!$course) {
            $this->error("Course not found: {$courseCode}");
            return 1;
        }

        // Read CSV file
        $handle = fopen($file, 'r');
        if (!$handle) {
            $this->error("Could not open file: {$file}");
            return 1;
        }

        // Read header row
        $headers = fgetcsv($handle);
        if (!$headers) {
            $this->error("Empty CSV file");
            fclose($handle);
            return 1;
        }

        // Expected headers
        $expectedHeaders = ['student_no', 'last_name', 'first_name', 'middle_name', 'year', 'section'];
        $missingHeaders = array_diff($expectedHeaders, $headers);
        
        if (!empty($missingHeaders)) {
            $this->error("Missing required headers: " . implode(', ', $missingHeaders));
            fclose($handle);
            return 1;
        }

        // Start processing rows
        $row = 2; // Start from row 2 (after headers)
        $successful = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            while (($data = fgetcsv($handle)) !== false) {
                $record = array_combine($headers, $data);
                
                // Format each name part with proper capitalization
                $lastName = ucwords(strtolower(trim($record['last_name'])));
                $firstName = ucwords(strtolower(trim($record['first_name'])));
                $middleName = !empty(trim($record['middle_name'])) 
                    ? ' ' . ucwords(strtolower(trim($record['middle_name'])))
                    : '';

                // Format the full name as "Lastname, Firstname Middlename"
                $fullName = $lastName . ', ' . $firstName . $middleName;

                // Set default section if blank
                $section = empty(trim($record['section'])) ? 'A' : trim($record['section']);
                
                // Prepare data for validation
                $validationData = [
                    'student_id' => $record['student_no'],
                    'name' => $fullName,
                    'year' => $record['year'],
                    'section' => $section,
                ];

                // Validate the record
                $validator = Validator::make($validationData, [
                    'name' => ['required', 'string', 'max:255'],
                    'student_id' => ['required', 'string'],
                    'year' => ['required', 'integer', Rule::in([1, 2, 3, 4])],
                    'section' => ['required', Rule::in(['A', 'B', 'C', 'D', 'E', 'F'])],
                ]);

                if ($validator->fails()) {
                    $failed++;
                    $errors[] = "Row {$row}: " . implode(', ', $validator->errors()->all());
                    $row++;
                    continue;
                }

                // Get or create section
                $sectionModel = Section::firstOrCreate([
                    'name' => $section,
                    'year_level' => $validationData['year'],
                    'course_id' => $course->id,
                ]);

                // Check if user exists
                $existingUser = User::where('student_id', $validationData['student_id'])->first();

                if ($existingUser) {
                    // Update existing user
                    $existingUser->update([
                        'name' => $validationData['name'],
                        'year' => $validationData['year'],
                        'course_id' => $course->id,
                        'section_id' => $sectionModel->id,
                    ]);
                    $this->info("Updated user: {$validationData['student_id']}");
                } else {
                    // Create new user
                    User::create([
                        'name' => $validationData['name'],
                        'username' => User::generateUsername($validationData['student_id']),
                        'student_id' => $validationData['student_id'],
                        'email' => null,
                        'password' => Hash::make(User::generatePassword($validationData['student_id'])),
                        'role' => User::ROLE_STUDENT,
                        'year' => $validationData['year'],
                        'contact_number' => null,
                        'course_id' => $course->id,
                        'section_id' => $sectionModel->id,
                    ]);
                    $this->info("Created new user: {$validationData['student_id']}");
                }

                $successful++;
                $row++;
            }

            if ($failed > 0) {
                throw new \Exception("Some records failed validation");
            }

            DB::commit();
            
            // Clear the welcome page cache
            Cache::forget(Fine::getFinesCacheKey());
            
            $this->info("Import completed successfully!");
            $this->info("Processed records: " . ($row - 2));
            $this->info("Successful: {$successful}");
            $this->info("Failed: {$failed}");
            
            if (!empty($errors)) {
                $this->error("Errors encountered:");
                foreach ($errors as $error) {
                    $this->error($error);
                }
            }

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Import failed: " . $e->getMessage());
            
            if (!empty($errors)) {
                $this->error("Validation errors:");
                foreach ($errors as $error) {
                    $this->error($error);
                }
            }
            
            return 1;
        } finally {
            fclose($handle);
        }
    }
} 