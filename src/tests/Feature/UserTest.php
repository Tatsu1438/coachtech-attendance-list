<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use App\Models\AttendanceRequest;

class UserTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function test_user_page_displays_current_datetime()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);

        $response = $this->actingAs($user)->get(route('user.start.work'));

        $response->assertStatus(200);
        $response->assertSee(now()->copy()->timezone('Asia/Tokyo')->format('Y年m月d日'));
        $response->assertSee(now()->copy()->timezone('Asia/Tokyo')->format('H:i'));
    }

    public function test_user_page_displays_default_working_status()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('user.start.work'));

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    public function test_check_user_working_status_is_working()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)->post(route('attendance.clock_in'));


        $response = $this->actingAs($user)->get(route('user.start.work'));

        $response->assertStatus(200);
        $response->assertSee('出勤中');

    }

    public function test_check_user_working_status_is_breaking()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)->post(route('attendance.clock_in'));
        $this->actingAs($user)->post(route('attendance.break_start'));


        $response = $this->actingAs($user)->get(route('user.start.work'));

        $response->assertStatus(200);
        $response->assertSee('休憩中');

    }

    public function test_check_user_working_status_is_end_working()
    {
        $user = User::factory()->create([
            'status' => '退勤済',
            'password' => bcrypt('password123')
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)->post(route('attendance.clock_in'));
        $this->actingAs($user)->post(route('attendance.break_start'));
        $this->actingAs($user)->post(route('attendance.break_end'));
        $this->actingAs($user)->post(route('attendance.clock_out'));

        $response = $this->actingAs($user)->get(route('user.start.work'));

        $response->assertStatus(200);
        $response->assertSee('退勤済');

    }

    public function test_user_page_displays_working_btn()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('user.start.work'));

        $response->assertStatus(200);
        $response->assertSee('出勤');
    }

    public function test_user_cannot_push_btn_when_already_finished()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($user)->post(route('attendance.clock_in'));
        $this->actingAs($user)->post(route('attendance.break_start'));
        $this->actingAs($user)->post(route('attendance.break_end'));
        $this->actingAs($user)->post(route('attendance.clock_out'));

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('user.start.work'));
        $response->assertStatus(200);

        $response->assertDontSee('出勤');
        $response->assertSee('お疲れさまでした。');
    }

    public function test_user_can_check_working_time()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);

        $this->actingAs($user)->post(route('attendance.clock_in'));

        $now = Carbon::parse('2025-10-16 09:00:00');

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $now->format('Y-m-d'),
            'clock_in' => '09:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('user.work.list'));

        $response->assertStatus(200);
        $response->assertSee($attendance->clock_in_time);
    }

    public function test_user_can_check_breaking_start()
    {
        $now = Carbon::parse('2025-10-16 09:00:00');
        $user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $now->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => null,
            'break_start' => null,
            'break_end' => null,
        ]);

        $this->actingAs($user)->post(route('attendance.clock_in'));

        $response = $this->actingAs($user)->get(route('user.start.work'));
        $response->assertStatus(200);
        $response->assertSee('休憩入');

        $this->actingAs($user)->post(route('attendance.break_start'));
        $response->assertStatus(200);
        $response = $this->actingAs($user)->get(route('user.start.work'));
        $response->assertSee('休憩中');
    }

    public function test_user_can_check_breaking_end()
    {
        $now = Carbon::parse('2025-10-16 09:00:00');
        $user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $now->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => null,
            'break_start' => null,
            'break_end' => null,
        ]);

        $this->actingAs($user)->post(route('attendance.clock_in'));

        $response = $this->actingAs($user)->get(route('user.start.work'));
        $response->assertStatus(200);
        $response->assertSee('休憩入');

        $this->actingAs($user)->post(route('attendance.break_start'));
        $response->assertStatus(200);
        $response = $this->actingAs($user)->get(route('user.start.work'));
        $response->assertSee('休憩中');
        $response->assertSee('休憩戻');

        $this->actingAs($user)->post(route('attendance.break_end'));
        $response = $this->actingAs($user)->get(route('user.start.work'));

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    public function test_user_can_check_breaking_time()
    {
        $now = Carbon::parse('2025-10-16 09:00:00');
        $user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);

        $this->actingAs($user)->post(route('attendance.clock_in'));
        $this->actingAs($user)->post(route('attendance.break_start'));
        $this->actingAs($user)->post(route('attendance.break_end'));


        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $now->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => null,
            'break_start' => '09:30:00',
            'break_end' => '10:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('user.work.list'));
        $response->assertStatus(200);

        $response->assertSee($attendance->formatted_break_time);
    }

    public function test_user_can_push_clock_out_btn()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($user)->post(route('attendance.clock_in'));
        $this->actingAs($user)->post(route('attendance.break_start'));
        $this->actingAs($user)->post(route('attendance.break_end'));


        $response = $this->actingAs($user)->get(route('user.start.work'));
        $response->assertStatus(200);
        $response->assertSee('退勤');

        $this->actingAs($user)->post(route('attendance.clock_out'));
        $response = $this->actingAs($user)->get(route('user.start.work'));
        $response->assertSee('お疲れさまでした。');
    }

    public function test_check_correct_clock_out_time()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($user)->post(route('attendance.clock_in'));
        $this->actingAs($user)->post(route('attendance.break_start'));
        $this->actingAs($user)->post(route('attendance.break_end'));
        $this->actingAs($user)->post(route('attendance.clock_out'));

        $attendance = Attendance::where('user_id', $user->id)
        ->where('work_date', now()->toDateString())
        ->first();

        $response = $this->actingAs($user)->get(route('user.work.list'));
        $response->assertStatus(200);
        $response->assertSee($attendance->clock_out_time);
    }

    public function test_check_all_correct_attendance_data()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($user)->post(route('attendance.clock_in'));
        $this->actingAs($user)->post(route('attendance.break_start'));
        $this->actingAs($user)->post(route('attendance.break_end'));
        $this->actingAs($user)->post(route('attendance.clock_out'));

        $attendance = Attendance::where('user_id', $user->id)
        ->where('work_date', now()->toDateString())
        ->first();

        $response = $this->actingAs($user)->get(route('user.work.list'));
        $response->assertStatus(200);

        $response->assertSee($attendance->formatted_date);
        $response->assertSee($attendance->clock_in_time );
        $response->assertSee($attendance->clock_out_time);
        $response->assertSee($attendance->formatted_break_time);
        $response->assertSee($attendance->formatted_total_time);
    }


    public function test_check_user_name_in_detail()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($user)->post(route('attendance.clock_in'));
        $this->actingAs($user)->post(route('attendance.break_start'));
        $this->actingAs($user)->post(route('attendance.break_end'));
        $this->actingAs($user)->post(route('attendance.clock_out'));

        $attendance = Attendance::where('user_id', $user->id)
        ->where('work_date', now()->toDateString())
        ->first();

        $response = $this->actingAs($user)->get(route('user.work.list.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);

        $response->assertSee($user->user_name);
    }

    public function test_check_date_time_in_detail()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($user)->post(route('attendance.clock_in'));
        $this->actingAs($user)->post(route('attendance.break_start'));
        $this->actingAs($user)->post(route('attendance.break_end'));
        $this->actingAs($user)->post(route('attendance.clock_out'));

        $attendance = Attendance::where('user_id', $user->id)
        ->where('work_date', now()->toDateString())
        ->first();

        $response = $this->actingAs($user)->get(route('user.work.list.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);

        $response->assertSee($attendance->work_year);
        $response->assertSee($attendance->work_month_day);
    }

    public function test_check_correct_work_time_in_detail()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('user.work.list.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);

        $response->assertSee('9:00');
        $response->assertSee('18:00');
    }

    public function test_check_correct_break_time_in_detail()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id'      => $user->id,
            'work_date'    => now()->toDateString(),
            'clock_in'     => '09:00:00',
            'clock_out'    => '18:00:00',
            'break_start'  => '12:00:00',
            'break_end'    => '13:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('user.work.list.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);

        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    public function test_user_can_see_current_month_data()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($user)->get(route('user.work.list'));
        $response->assertStatus(200);
        $response->assertSee(Carbon::now()->format('Y/m'));
    }

    public function test_user_can_check_previous_month_data()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($user)->get(route('user.work.list'));
        $response->assertStatus(200);

        $currentMonth = Carbon::now();
        $previousMonth = $currentMonth->copy()->subMonth()->format('Y-m');

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::parse($previousMonth . '-15'),
        ]);

        $response = $this->actingAs($user)->get(route('common.month_select', [
            'id' => $user->id,
            'date' => $previousMonth
        ]));
        $response->assertStatus(200);
        $response->assertSee(str_replace('-', '/', $previousMonth));

    }


    public function test_user_can_check_next_month_data()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($user)->get(route('user.work.list'));
        $response->assertStatus(200);

        $currentMonth = Carbon::now();
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::parse($nextMonth . '-15'),
        ]);

        $response = $this->actingAs($user)->get(route('common.month_select', [
            'id' => $user->id,
            'date' => $nextMonth
        ]));
        $response->assertStatus(200);
        $response->assertSee(str_replace('-', '/', $nextMonth));
    }


    public function test_user_can_check_month_data_detail()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($user)->get(route('user.work.list'));
        $response->assertStatus(200);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::now()
        ]);

        $response = $this->actingAs($user)->get(route('user.work.list.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');

    }

    public function test_clock_in_later_than_clock_out_shows_error()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);


        $response = $this->actingAs($user)->get(route('user.work.list.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->put(route('user.attendance.update', $attendance->id), [
            'clock_in' => '18:00',
            'clock_out' => '10:00',
            'request_reason' => 'テスト',
        ]);

        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間が不適切な値です',
        ]);
    }


    public function test_break_start_later_than_clock_out_shows_error()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);


        $response = $this->actingAs($user)->get(route('user.work.list.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->put(route('user.attendance.update', $attendance->id), [
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


    public function test_break_end_later_than_clock_out_shows_error()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);


        $response = $this->actingAs($user)->get(route('user.work.list.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->put(route('user.attendance.update', $attendance->id), [
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

    public function test_request_reason_required_validation()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);


        $response = $this->actingAs($user)->get(route('user.work.list.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->put(route('user.attendance.update', $attendance->id), [
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

    public function test_user_can_submit_attendance_request_and_user_can_see_it()
    {
        $user = User::factory()->create([
            'user_name' => '藤井達矢',
            'password' => bcrypt('password123'),
        ]);


        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);


        $response = $this->actingAs($user)->put(route('user.attendance.update', $attendance->id), [
            'clock_in' => '09:30',
            'clock_out' => '18:30',
            'break_start' => '12:00',
            'break_end' => '13:00',
            'request_reason' => '出勤時間を修正します',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendance_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'request_status' => '承認待ち',
        ]);

        $response = $this->actingAs($user)->get(route('user.ask.request'));
        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee('藤井達矢');
        $response->assertSee('出勤時間を修正します');
    }

    public function test_user_can_submit_attendance_request_and_admin_can_see_it()
    {
        $user = User::factory()->create([
            'user_name' => '藤井達矢',
            'password' => bcrypt('password123'),
        ]);


        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);


        $response = $this->actingAs($user)->put(route('user.attendance.update', $attendance->id), [
            'clock_in' => '09:30',
            'clock_out' => '18:30',
            'break_start' => '12:00',
            'break_end' => '13:00',
            'request_reason' => '出勤時間を修正します',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendance_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'request_status' => '承認待ち',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin12345'),
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.request.list'));
        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee('藤井達矢');
        $response->assertSee('出勤時間を修正します');
    }

    public function test_user_can_see_all_approved_requests_in_approved_list()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('admin12345'),
        ]);

        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $attendance1 = Attendance::factory()->create(['user_id' => $user->id]);
        $attendance2 = Attendance::factory()->create(['user_id' => $user->id]);


        $request1 = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance1->id,
            'user_id' => $user->id,
            'request_status' => '承認済み',
            'request_reason' => 'テスト申請①',
        ]);

        $request2 = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance2->id,
            'user_id' => $user->id,
            'request_status' => '承認済み',
            'request_reason' => 'テスト申請②',
        ]);

        $response = $this->actingAs($user)->get(route('user.ask.request'));
        $response->assertStatus(200);

        $response->assertSee('テスト申請①');
        $response->assertSee('テスト申請②');
    }

    public function test_user_can_push_break_btn_again_and_again()
    {
        $user = User::factory()->create(['status' => '出勤中']);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
        ]);

        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '12:30:00',
        ]);

        $response = $this->actingAs($user)->get(route('user.start.work'));
        $response->assertStatus(200);
        $response = $this->actingAs($user)->post(route('attendance.break_start'));
        $response->assertRedirect(route('user.start.work'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => '休憩中',
        ]);

        $response = $this->actingAs($user)->post(route('attendance.break_end'));
        $response->assertRedirect(route('user.start.work'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => '出勤中',
        ]);

        $response = $this->actingAs($user)->post(route('attendance.break_start'));
        $response->assertRedirect(route('user.start.work'));

        $this->assertEquals(2, $attendance->breaks()->count());
    }

    public function test_user_can_see_break_time_in_the_attendance_list()
    {
        $user = User::factory()->create(['status' => '出勤中']);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in' => '10:00:00',
            'break_start' => '11:00:00',
            'break_end' => '11:30:00',
            'clock_in' => '23:00:00',
        ]);

        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '12:30:00',
        ]);

        $this->actingAs($user)->post(route('attendance.break_end'));

        $response = $this->actingAs($user)->get(route('user.work.list'));
        $response->assertStatus(200);

        $todayFormatted = Carbon::today()->format('m/d');
        $response->assertSee($todayFormatted);
        $response->assertSee('1:00');
    }

}
