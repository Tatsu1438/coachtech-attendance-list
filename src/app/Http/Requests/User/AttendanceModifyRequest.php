<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceModifyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $isAdmin = auth('admin')->check();

        return [
            'clock_in'  => ['required', 'date_format:H:i', 'before:clock_out'],
            'clock_out' => ['required', 'date_format:H:i', 'after:clock_in'],

            'break_start' => ['nullable', 'date_format:H:i', 'before:break_end', 'before:clock_out'],
            'break_end'   => ['nullable', 'date_format:H:i', 'after:break_start', 'before:clock_out'],

            'request_reason' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時間を入力してください',
            'clock_in.before' => '出勤時間が不適切な値です',
            'clock_out.required' => '退勤時間を入力してください',
            'clock_in.date_format' => '出勤時間の形式が不正です（例:09:00）',
            'clock_out.date_format' => '退勤時間の形式が不正です（例:18:00）',
            'clock_out.after' => '退勤時間は出勤時間より後の時刻を入力してください',

            'break_start.before' => '休憩時間が不適切な値です',
            'break_end.after' => '休憩時間もしくは退勤時間が不適切な値です',
            'break_end.before' => '休憩時間もしくは退勤時間が不適切な値です',
            'break_start.date_format' => '休憩開始時間の形式が不正です（例:12:00）',
            'break_end.date_format' => '休憩終了時間の形式が不正です（例:13:00）',

            'request_reason.required' => '備考を記入してください',
        ];
    }
}