<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\AttendanceRequest;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    /** @test */
    public function admin_can_see_all_data_of_users()
    {
        $today = \Carbon\Carbon::today()->toDateString();

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin12345'),
        ]);

        $users = User::factory(3)->create();

        foreach ($users as $user) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => $today,
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
                'break_start' => '09:00:00',
                'break_end' => '10:00:00',
                'break_time' => '1:00:00',
            ]);
        }

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.list'));
        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSee($user->user_name);
            $response->assertSee('9:00');
            $response->assertSee('18:00');
            $response->assertSee('1:00');
            $response->assertSee('8:00');
        }
    }

    /** @test */
    public function admin_list_displays_current_month()
    {
        $today = \Carbon\Carbon::today();
        $currentMonth = $today->format('Y-m');

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin12345'),
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.list'));
        $response->assertStatus(200);

        $response->assertSee($currentMonth);
    }

    /** @test */
    public function admin_list_can_push_yesterday_btn()
    {
        $today = \Carbon\Carbon::today();
        $previousDay = $today->copy()->subDay();

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin12345'),
        ]);

        $response = $this->actingAs($admin, 'admin')
                        ->get(route('attendance.day_select', ['date' => $previousDay->toDateString()]));
        $response->assertStatus(200);

        $response->assertSee($previousDay->format('Y年n月j日'));
    }


    /** @test */
    public function admin_list_can_push_tomorrow_btn()
    {
        $today = \Carbon\Carbon::today();
        $nextDay = $today->copy()->addDay();

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin12345'),
        ]);

        $response = $this->actingAs($admin, 'admin')
                        ->get(route('attendance.day_select', ['date' => $nextDay->toDateString()]));
        $response->assertStatus(200);


        $response->assertSee($nextDay->format('Y年n月j日'));
    }


    /** @test */
    public function admin_list_detail_displays_selected_one()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin12345'),
        ]);

        $user = User::factory()->create([
            'user_name' => '藤井達矢',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-10-17',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);


        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);

        $response->assertSee($attendance->id);
        $response->assertSee($attendance->work_year);
        $response->assertSee($attendance->work_month_day);
        $response->assertSee('藤井達矢');
        $response->assertSee($attendance->clock_in_time);
        $response->assertSee($attendance->clock_out_time);
    }

    /** @test */
    public function staff_list_can_displays_all_staff()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin12345'),
        ]);

        $users = User::factory()->count(3)->create();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.staff.list'));
        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSee($user->user_name);
            $response->assertSee($user->email);
        }
    }

     /** @test */
    public function staff_list_can_displays_all_staff_attendance_data()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin12345'),
        ]);

        $user = User::factory()->create([
            'user_name' => '山田太郎',
            'email' => 'taro@example.com',
        ]);

        $attendances = Attendance::factory()->createMany([
            [
                'user_id' => $user->id,
                'work_date' => '2025-10-17',
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
            ],
            [
                'user_id' => $user->id,
                'work_date' => '2025-10-18',
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
            ],
            [
                'user_id' => $user->id,
                'work_date' => '2025-10-19',
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
            ],
        ]);


        $response = $this->actingAs($admin, 'admin')->get(route('admin.staff.detail', ['id' => $user->id]));
        $response->assertStatus(200);

        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->formatted_date);
            $response->assertSee($attendance->clock_in_time);
            $response->assertSee($attendance->clock_out_time);
        }
    }

    public function test_admin_can_see_the_list_of_previous_month_pushing_previous_btn()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin12345'),
        ]);

        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.staff.detail', ['id' => $user->id, ]));
        $response = $this->actingAs($admin, 'admin')->get(route('common.month_select', ['id' => $user->id]));

        $currentMonth = Carbon::now()->format('Y-m');
        $previousMonth = Carbon::now()->subMonth()->format('Y-m');

        $response = $this->get(route('admin.staff.detail', [
            'id' => $user->id,
            'yearMonth' => $previousMonth,
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('administrator.staff_detail');
        $response->assertViewHas('previousMonth', $previousMonth);
    }

    public function test_admin_can_see_the_list_of_next_month_pushing_next_btn()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin12345'),
        ]);

        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.staff.detail', ['id' => $user->id, ]));
        $response = $this->actingAs($admin, 'admin')->get(route('common.month_select', ['id' => $user->id]));

        $currentMonth = Carbon::now()->format('Y-m');
        $nextMonth = Carbon::now()->addMonth()->format('Y-m');

        $response = $this->get(route('admin.staff.detail', [
            'id' => $user->id,
            'yearMonth' => $nextMonth,
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('administrator.staff_detail');
        $response->assertViewHas('nextMonth', $nextMonth);
    }

    public function test_admin_can_see_the_list_of_detail_month_pushing_detail_btn()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin12345'),
        ]);

        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.staff.detail', ['id' => $user->id, ]));

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::now()->toDateString(),
        ]);

        $response = $this->get(route('admin.attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertViewIs('administrator.attendance_detail');
    }

    public function test_admin_clock_in_later_than_clock_out_shows_error()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin12345'),
        ]);

        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);


        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);

        $response = $this->actingAs($admin, 'admin')->put(route('admin.attendance.update', $attendance->id), [
            'clock_in' => '18:00',
            'clock_out' => '10:00',
            'request_reason' => 'テスト',
        ]);

        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間が不適切な値です',
        ]);
    }


    public function test_admin_break_start_later_than_clock_out_shows_error_()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin12345'),
        ]);

        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);


        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);

        $response = $this->actingAs($admin, 'admin')->put(route('admin.attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => '19:00',
            'break_end' => '19:30',
            'request_reason' => 'テスト',
        ]);

        $response->assertSessionHasErrors([
            'break_start' => '休憩時間が不適切な値です',
        ]);
    }


    public function test_admin_break_end_later_than_clock_out_shows_error()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin12345'),
        ]);

        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);


        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);

        $response = $this->actingAs($admin, 'admin')->put(route('admin.attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => '17:00',
            'break_end' => '19:00',
            'request_reason' => 'テスト',
        ]);

        $response->assertSessionHasErrors([
            'break_end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_admin_request_reason_required_validation()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin12345'),
        ]);

        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);


        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);

        $response = $this->actingAs($admin, 'admin')->put(route('admin.attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'break_start' => '12:00',
            'break_end' => '13:00',
            'request_reason' => '',
        ]);

        $response->assertSessionHasErrors([
            'request_reason' => '備考を記入してください',
        ]);
    }

    public function test_not_approved_requests_are_displayed_in_request_list()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin12345'),
        ]);

        AttendanceRequest::factory()->count(3)->create(['request_status' => '承認待ち']);
        AttendanceRequest::factory()->count(2)->create(['request_status' => '承認済み']);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.request.list'));

        $response->assertStatus(200)
                ->assertSee('申請一覧')
                ->assertSee('承認待ち');
    }

    public function test_approved_requests_are_displayed_in_request_list()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin12345'),
        ]);

        AttendanceRequest::factory()->count(2)->create(['request_status' => '承認済み']);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.request.list'));

        $response->assertStatus(200)
                ->assertSee('承認済み');
    }

    public function test_admin_can_see_requests_in_detail_in_request_list()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin12345'),
        ]);

        $attendance = Attendance::factory()->create();
        $request = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'request_reason' => '出勤時刻の誤り',
            'request_status' => '承認待ち'
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.request.approve', $request->id));

        $response->assertStatus(200)
                ->assertSee('出勤時刻の誤り');
    }


    public function test_if_requests_are_approved_list_can__update_list()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin12345'),
        ]);

        $attendance = Attendance::factory()->create([
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00'
        ]);

        $request = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'clock_in' => '08:30:00',
            'clock_out' => '17:30:00',
            'request_status' => '承認待ち'
        ]);

        $response = $this->actingAs($admin, 'admin')->put(route('admin.request.permitted', $request->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('attendance_requests', [
            'id' => $request->id,
            'request_status' => '承認済み'
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '08:30:00',
            'clock_out' => '17:30:00'
        ]);
    }


}
