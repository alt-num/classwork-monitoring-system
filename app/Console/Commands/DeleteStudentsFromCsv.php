<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\ClassworkActivity;
use App\Models\Fine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DeleteStudentsFromCsv extends Command
{
    protected $signature = 'students:delete {file : Path to the CSV file}';
    protected $description = 'Delete students based on student IDs from a CSV file';

    public function handle()
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
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

        // Verify the student_no column exists
        if (!in_array('student_no', $headers)) {
            $this->error("CSV file must contain a 'student_no' column");
            fclose($handle);
            return 1;
        }

        // Start processing rows
        $row = 2; // Start from row 2 (after headers)
        $successful = 0;
        $notFound = 0;
        $skippedSecretaries = 0;
        $studentIds = [];

        while (($data = fgetcsv($handle)) !== false) {
            $record = array_combine($headers, $data);
            $studentIds[] = $record['student_no'];
        }

        fclose($handle);

        if (empty($studentIds)) {
            $this->error("No student IDs found in the CSV file");
            return 1;
        }

        // First, check for secretaries and their responsibilities
        $secretaries = User::whereIn('student_id', $studentIds)
            ->where('role', User::ROLE_SECRETARY)
            ->get();

        if ($secretaries->isNotEmpty()) {
            $this->error("\nCannot delete the following secretaries:");
            foreach ($secretaries as $secretary) {
                $activitiesCount = ClassworkActivity::where('secretary_id', $secretary->id)->count();
                $this->error("- {$secretary->name} (ID: {$secretary->student_id})");
                $this->error("  Has created {$activitiesCount} activities");
                $this->error("  Please reassign or delete their activities first");
            }
            
            if (!$this->confirm("Do you want to continue deleting other students (skipping secretaries)?")) {
                $this->info("Operation cancelled");
                return 0;
            }
        }

        // Confirm deletion for remaining students
        $studentsToDelete = User::whereIn('student_id', $studentIds)
            ->where('role', User::ROLE_STUDENT)
            ->count();

        if ($studentsToDelete === 0) {
            $this->error("No eligible students found to delete");
            return 1;
        }

        if (!$this->confirm("Are you sure you want to delete {$studentsToDelete} students?")) {
            $this->info("Operation cancelled");
            return 0;
        }

        DB::beginTransaction();
        try {
            foreach ($studentIds as $studentId) {
                $user = User::where('student_id', $studentId)->first();

                if (!$user) {
                    $notFound++;
                    $this->warn("ID not found: {$studentId}");
                    continue;
                }

                if ($user->role === User::ROLE_SECRETARY) {
                    $skippedSecretaries++;
                    continue;
                }

                if ($user->role === User::ROLE_STUDENT) {
                    $user->delete();
                    $successful++;
                }
            }

            DB::commit();
            
            // Clear the fines cache after deleting users
            Cache::forget(Fine::getFinesCacheKey());
            
            $this->info("\nDeletion completed!");
            $this->info("Total IDs processed: " . count($studentIds));
            $this->info("Successfully deleted: {$successful}");
            $this->info("Skipped secretaries: {$skippedSecretaries}");
            $this->info("Not found: {$notFound}");

            if ($skippedSecretaries > 0) {
                $this->warn("\nNote: {$skippedSecretaries} secretaries were skipped.");
                $this->warn("To delete secretaries, first reassign or delete their activities,");
                $this->warn("then remove their secretary role via the admin dashboard.");
            }

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Deletion failed: " . $e->getMessage());
            return 1;
        }
    }
} 