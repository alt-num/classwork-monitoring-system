<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('name');
            $table->string('role')->after('password');
            $table->string('student_id')->nullable()->unique()->after('role');
            $table->unsignedTinyInteger('year')->nullable()->after('student_id');
            $table->string('contact_number')->nullable()->after('year');
            $table->foreignId('course_id')->nullable()->after('contact_number')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->after('course_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropForeign(['section_id']);
            $table->dropColumn([
                'username',
                'role',
                'student_id',
                'year',
                'contact_number',
                'course_id',
                'section_id',
            ]);
        });
    }
}; 