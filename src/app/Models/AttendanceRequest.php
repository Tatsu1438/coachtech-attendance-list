<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'clock_in',
        'clock_out',
        'break_start',
        'break_end',
        'request_reason',
        'request_status',
    ];

    protected $casts = [
        'clock_in' => 'datetime:H:i',
        'clock_out' => 'datetime:H:i',
        'break_start' => 'datetime:H:i',
        'break_end' => 'datetime:H:i',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function requestBreaks()
    {
        return $this->hasMany(AttendanceRequestBreak::class, 'attendance_request_id');
    }

    public function breaks()
    {
        return $this->hasMany(AttendanceRequestBreak::class, 'attendance_request_id');
    }
}
