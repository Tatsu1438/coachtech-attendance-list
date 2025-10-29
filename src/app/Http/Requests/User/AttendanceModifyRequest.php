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

        if ($isAdmin) {
            return [
                'clock_in'  => ['required', 'date_format:H:i', 'before:clock_out'],
                'clock_out' => ['required', 'date_format:H:i', 'after:clock_in'],

                'break_start' => ['nullable', 'date_format:H:i', 'before:break_end', 'before:clock_out'],
                'break_end'   => ['nullable', 'date_format:H:i', 'after:break_start', 'before:clock_out'],


                'request_reason' => ['required', 'string', 'max:255'],
            ];
        }

        return [
            'clock_in'  => ['required', 'date_format:H:i', 'before:clock_out'],
            'clock_out' => ['required', 'date_format:H:i', 'after:clock_in'],

            'break_start' => ['nullable', 'date_format:H:i', 'before:break_end', 'before:clock_out'],
            'break_end'   => ['nullable', 'date_format:H:i', 'after:break_start', 'before:clock_out'],

            // 休憩2〜4
            'break_start_2' => ['nullable', 'date_format:H:i', 'before:break_end_2', 'before:clock_out'],
            'break_end_2'   => ['nullable', 'date_format:H:i', 'after:break_start_2', 'before:clock_out'],

            'break_start_3' => ['nullable', 'date_format:H:i', 'before:break_end_3', 'before:clock_out'],
            'break_end_3'   => ['nullable', 'date_format:H:i', 'after:break_start_3', 'before:clock_out'],

            'break_start_4' => ['nullable', 'date_format:H:i', 'before:break_end_4', 'before:clock_out'],
            'break_end_4'   => ['nullable', 'date_format:H:i', 'after:break_start_4', 'before:clock_out'],

            'request_reason' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時間を入力してください',
            'clock_in.before' => '出勤時間が不適切な値です',
            'clock_out.required' => '退勤時間を入力してください',

            'break_start.before' => '休憩時間が不適切な値です',
            'break_end.before' => '休憩時間もしくは退勤時間が不適切な値です',

            // 休憩2〜4
            'break_start_2.before' => '休憩2の開始時間が不適切です',
            'break_end_2.after'    => '休憩2の終了時間が不適切です',

            'break_start_3.before' => '休憩3の開始時間が不適切です',
            'break_end_3.after'    => '休憩3の終了時間が不適切です',

            'break_start_4.before' => '休憩4の開始時間が不適切です',
            'break_end_4.after'    => '休憩4の終了時間が不適切です',

            'request_reason.required' => '備考を記入してください',
        ];
    }
}