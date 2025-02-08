<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'year_level',
        'course_id'
    ];

    // Relationships
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function activities()
    {
        return $this->hasMany(ClassworkActivity::class);
    }

    public function secretaries()
    {
        return $this->hasMany(User::class)->where('role', User::ROLE_SECRETARY);
    }

    public function students()
    {
        return $this->hasMany(User::class)->where('role', User::ROLE_STUDENT);
    }
}
