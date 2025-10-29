<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),

            'work_date' => $this->faker->date(),

            'clock_in' => null,
            'clock_out' => null,
            'break_start' => null,
            'break_end' => null,

            'break_time' => null,
            'total_time' => null,

            'request_status' => '修正なし',
            'request_reason' => null,

            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
