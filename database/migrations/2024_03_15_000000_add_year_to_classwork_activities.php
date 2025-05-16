<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classwork_activities', function (Blueprint $table) {
            $table->unsignedTinyInteger('year')->after('section_id');
        });

        // Update existing activities with the year from their sections
        DB::statement('
            UPDATE classwork_activities
            SET year = (
                SELECT year_level 
                FROM sections 
                WHERE sections.id = classwork_activities.section_id
            )
        ');
    }

    public function down(): void
    {
        Schema::table('classwork_activities', function (Blueprint $table) {
            $table->dropColumn('year');
        });
    }
}; 