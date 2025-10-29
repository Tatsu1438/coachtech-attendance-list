<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\User;
use App\Models\AttendanceRequest;

class AttendanceBreakFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),
            'break_start' => $this->faker->time('H:i:s', '13:00:00'),
            'break_end'   => $this->faker->time('H:i:s', '14:00:00'),
            'break_number' => 1,
        ];
    }
}
