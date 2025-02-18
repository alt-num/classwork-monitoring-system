<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{
    use HasFactory;

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
    }
} 