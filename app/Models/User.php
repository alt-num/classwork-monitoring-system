<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const ROLE_ADMIN = 'admin';
    const ROLE_SECRETARY = 'secretary';
    const ROLE_STUDENT = 'student';

    const YEAR_FIRST = 1;
    const YEAR_SECOND = 2;
    const YEAR_THIRD = 3;
    const YEAR_FOURTH = 4;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'student_id',
        'year',
        'contact_number',
        'course_id',
        'section_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'year' => 'integer',
    ];

    // Generate default username from student ID
    public static function generateUsername(string $studentId): string
    {
        return 'bc-' . $studentId;
    }

    // Generate default password from student ID
    public static function generatePassword(string $studentId): string
    {
        return $studentId;
    }

    // Reset user credentials to defaults
    public function resetCredentials(): void
    {
        $this->update([
            'username' => self::generateUsername($this->student_id),
            'password' => Hash::make(self::generatePassword($this->student_id))
        ]);
    }

    // Toggle secretary role
    public function toggleSecretaryRole(): void
    {
        // Start a database transaction
        DB::beginTransaction();

        try {
            if ($this->role === self::ROLE_SECRETARY) {
                // If removing secretary role, find the new secretary (if exists)
                $newSecretary = User::where('role', self::ROLE_SECRETARY)
                    ->where('course_id', $this->course_id)
                    ->where('section_id', $this->section_id)
                    ->where('year', $this->year)
                    ->where('id', '!=', $this->id)
                    ->first();

                // Update activities to be owned by new secretary if exists
                if ($newSecretary) {
                    ClassworkActivity::where('secretary_id', $this->id)
                        ->update(['secretary_id' => $newSecretary->id]);
                }
            } else {
                // If becoming a secretary, take over existing activities for this section
                ClassworkActivity::where('section_id', $this->section_id)
                    ->where('course_id', $this->course_id)
                    ->update(['secretary_id' => $this->id]);
            }

            // Toggle the role
        $this->update([
            'role' => $this->role === self::ROLE_SECRETARY ? self::ROLE_STUDENT : self::ROLE_SECRETARY
        ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // Add username as the authentication field
    public function username()
    {
        return 'username';
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function activities()
    {
        return $this->hasMany(ClassworkActivity::class, 'created_by');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class, 'student_id');
    }

    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isSecretary()
    {
        return $this->role === self::ROLE_SECRETARY;
    }

    public function isStudent()
    {
        return $this->role === self::ROLE_STUDENT;
    }

    public function getYearLevelAttribute()
    {
        return match($this->year) {
            self::YEAR_FIRST => '1st Year',
            self::YEAR_SECOND => '2nd Year',
            self::YEAR_THIRD => '3rd Year',
            self::YEAR_FOURTH => '4th Year',
            default => 'N/A'
        };
    }
}
