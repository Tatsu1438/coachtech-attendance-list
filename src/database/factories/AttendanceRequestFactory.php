<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceRequest;
use App\Models\Attendance;
use App\Models\User;

class AttendanceRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     *
     */

    protected $model = AttendanceRequest::class;

    public function definition(): array
    {
        return [
            'attendance_id' => Attendance::factory(),
            'user_id' => User::factory(),
            'clock_in' => $this->faker->time('H:i:s'),
            'clock_out' => $this->faker->time('H:i:s'),
            'break_start' => $this->faker->optional()->time('H:i:s'),
            'break_end' => $this->faker->optional()->time('H:i:s'),
            'request_reason' => $this->faker->sentence(3),
            'request_status' => $this->faker->randomElement(['承認待ち', '承認済み']),
        ];
    }
}
