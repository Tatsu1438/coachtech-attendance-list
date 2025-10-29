<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;
    protected $table = 'attendances';

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'break_time',
        'total_time',
        'break_start',
        'break_end',
        'request_reason',
        'request_status',
    ];

    protected $dates = [
        'work_date',
        'clock_in',
        'clock_out',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    public function requests()
    {
        return $this->hasMany(AttendanceRequest::class);
    }



    public function getCalculatedTotalTimeAttribute()
    {
        if ($this->clock_in && $this->clock_out) {
            $diff = $this->clock_out->diffInMinutes($this->clock_in) - $this->break_time;
            return max($diff, 0);
        }
        return 0;
    }

    public function getClockInTimeAttribute()
    {
        return $this->clock_in ? $this->clock_in->format('H:i') : '';
    }


    public function getClockOutTimeAttribute()
    {
        return $this->clock_out ? $this->clock_out->format('H:i') : '';
    }

    public function getWorkYearAttribute()
    {
        return $this->work_date ? $this->work_date->format('Y年') : '';
    }

    public function getWorkMonthDayAttribute()
    {
        return $this->work_date ? $this->work_date->format('n月j日') : '';
    }

    public function getFormattedDateAttribute()
    {
        if (!$this->work_date) return '';

        $date = Carbon::parse($this->work_date);
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];


        return $date->format('m/d') . '(' . $weekdays[$date->dayOfWeek] . ')';
    }

    public function getFormattedBreakTimeAttribute()
    {
        if (!$this->break_time) return '';

        list($hours, $minutes, $seconds) = explode(':', $this->break_time);

        $hours = (int)$hours;
        $minutes = (int)$minutes;

        return sprintf('%d:%02d', $hours, $minutes);
    }

    public function getFormattedBreakPeriodAttribute()
    {
        if (!$this->break_start || !$this->break_end) {
            return '';
        }

        $start = Carbon::createFromFormat('H:i:s', $this->break_start);
        $end = Carbon::createFromFormat('H:i:s', $this->break_end);

        return ltrim($start->format('G:i'), '0') . '〜' . ltrim($end->format('G:i'), '0');
    }

    public function getFormattedBreakStartAttribute()
    {
        if (!$this->break_start) return '';
        $start = Carbon::createFromFormat('H:i:s', $this->break_start);
        return ltrim($start->format('G:i'), '0');
    }

    public function getFormattedBreakEndAttribute()
    {
        if (!$this->break_end) return '';
        $end = Carbon::createFromFormat('H:i:s', $this->break_end);
        return ltrim($end->format('G:i'), '0');
    }

    public function getFormattedTotalTimeAttribute()
    {
        if (!$this->total_time) return '';

        list($hours, $minutes, $seconds) = explode(':', $this->total_time);

        $hours = (int)$hours;
        $minutes = (int)$minutes;

        return sprintf('%d:%02d', $hours, $minutes);
    }
}