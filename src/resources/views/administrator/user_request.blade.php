@extends('layouts.admin-menu-layout')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/administrator/user-request.css') }}">
@endsection

@section('content')
    <div class="request-list">
        <div class="title-box">
            <div class="line-1"></div>
            <h2 class="request">申請一覧</h2>
        </div>
        <div class="approve-btn">
            <div>
                <button class="approve-wait">承認待ち</button>
            </div>
            <div>
                <button class="approve-done">承認済み</button>
            </div>
        </div>
        <div class="line-2"></div>
        <div class="table-box">
            <table>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
                @foreach($requests as $request)
                    <tr class="request-row {{ $request->request_status === '承認待ち' ? 'status-wait' : 'status-done' }}">
                        <td>{{ $request->request_status }}</td>
                        <td>{{ $request->user->user_name }}</td>
                        <td>{{ optional($request->attendance)->work_date?->format('Y/m/d') ?? '未設定' }}</td>
                        <td>{{ $request->request_reason }}</td>
                        <td>{{ optional($request->attendance)->work_date?->format('Y/m/d') }}</td>
                        <td><a href="{{ route('admin.request.approve', $request->id) }}">詳細</td>
                    </tr>
                @endforeach
                @if($requests->isEmpty())
                    <tr class="no-request" style="display:none;">
                        <td colspan="6">申請はありません</td>
                    </tr>
                @endif
            </table>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const waitBtn = document.querySelector('.approve-wait');
            const doneBtn = document.querySelector('.approve-done');
            const rows = document.querySelectorAll('.request-row');

            rows.forEach(row => {
                row.style.display = row.classList.contains('status-wait') ? '' : 'none';
            });
            waitBtn.classList.add('active');
            doneBtn.classList.remove('active');

            waitBtn.addEventListener('click', function () {
                waitBtn.classList.add('active');
                doneBtn.classList.remove('active');
                rows.forEach(row => {
                    row.style.display = row.classList.contains('status-wait') ? '' : 'none';
                });
            });

            doneBtn.addEventListener('click', function () {
                doneBtn.classList.add('active');
                waitBtn.classList.remove('active');
                rows.forEach(row => {
                    row.style.display = row.classList.contains('status-done') ? '' : 'none';
                });
            });
        });

    </script>

@endsection