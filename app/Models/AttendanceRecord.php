<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'classwork_activity_id',
        'status',
        'remarks',
        'recorded_by'
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function classworkActivity()
    {
        return $this->belongsTo(ClassworkActivity::class);
    }

    public function fine()
    {
        return $this->hasOne(Fine::class);
    }
}
