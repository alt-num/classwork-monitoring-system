<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
     // Run the migrations.
     
    public function up(): void
    {
        // First, update any 'late' records to 'absent'
        DB::table('attendance_records')
            ->where('status', 'late')
            ->update(['status' => 'absent']);

        // For SQLite, we need to create a new table and copy data
        Schema::create('attendance_records_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('classwork_activity_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('absent')->check("status IN ('present', 'absent', 'organizer')");
            $table->text('remarks')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['student_id', 'classwork_activity_id']);
        });

        // Copy data to new table
        DB::statement('INSERT INTO attendance_records_new SELECT * FROM attendance_records');

        // Drop old table and rename new one
        Schema::drop('attendance_records');
        Schema::rename('attendance_records_new', 'attendance_records');
    }

    
     // Reverse the migrations.
     
    public function down(): void
    {
        // For SQLite, create a new table and copy data back
        Schema::create('attendance_records_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('classwork_activity_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('absent')->check("status IN ('present', 'absent', 'organizer')");
            $table->text('remarks')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['student_id', 'classwork_activity_id']);
        });

        // Copy data to new table
        DB::statement('INSERT INTO attendance_records_new SELECT * FROM attendance_records');

        // Drop old table and rename new one
        Schema::drop('attendance_records');
        Schema::rename('attendance_records_new', 'attendance_records');
    }
}; 