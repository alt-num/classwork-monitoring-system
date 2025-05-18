<?php

namespace App\Models;

use App\Models\Traits\HasCalendarYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Fine extends Model
{
    use HasFactory, HasCalendarYear;

    protected $fillable = [
        'student_id',
        'attendance_record_id',
        'amount',
        'is_paid',
        'paid_at',
        'payment_reference',
        'notes',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    // Define which field to use for year filtering
    protected $yearFilterField = 'created_at';

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function attendanceRecord()
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    public function markAsPaid($paymentReference = null, $notes = null)
    {
        $this->update([
            'is_paid' => true,
            'paid_at' => now(),
            'payment_reference' => $paymentReference,
            'notes' => $notes,
        ]);

        // Clear the courses.fines cache with year
        $this->clearFinesCache();
    }

    /**
     * Clear the fines cache for all years
     */
    protected function clearFinesCache()
    {
        // Clear current year
        Cache::forget($this->getFinesCacheKey());
        
        // Also clear previous year during January to ensure proper transitions
        if (now()->month === 1) {
            Cache::forget($this->getFinesCacheKey(now()->subYear()->year));
        }
    }

    /**
     * Get the cache key for fines summary
     */
    public static function getFinesCacheKey(?int $year = null): string
    {
        $year = $year ?? now()->year;
        return "courses.fines.{$year}";
    }

    protected static function booted()
    {
        static::created(function ($fine) {
            $fine->clearFinesCache();
        });
        static::deleted(function ($fine) {
            $fine->clearFinesCache();
        });
        static::updated(function ($fine) {
            if ($fine->wasChanged(['is_paid', 'amount', 'attendance_record_id', 'student_id'])) {
                $fine->clearFinesCache();
            }
        });
    }
} 