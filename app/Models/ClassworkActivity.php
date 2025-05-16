<?php

namespace App\Models;

use App\Models\Traits\HasCalendarYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassworkActivity extends Model
{
    use HasFactory, HasCalendarYear;

    protected $fillable = [
        'title',
        'description',
        'due_date',
        'secretary_id',
        'course_id',
        'section_id',
    ];

    protected $casts = [
        'due_date' => 'datetime',
    ];

    // Define which field to use for year filtering
    protected $yearFilterField = 'due_date';

    // Relationships
    public function secretary()
    {
        return $this->belongsTo(User::class, 'secretary_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }
}
