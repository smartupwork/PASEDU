<p>
    Hi Partner Helpdesk. a partner from <strong>{{ $activity_progress->partner_name }}</strong> has requested a {{ $report_type }} with following details for student:
</p>
<table>
    <tr>
        <th style="text-align: right;">Student Name:</th>
        <td>{{ $activity_progress->subject }}</td>
    </tr>
    <tr>
        <th style="text-align: right;">Email:</th>
        <td>{{ $activity_progress->email }}</td>
    </tr>
    <tr>
        <th style="text-align: right;">Phone:</th>
        <td>{{ $activity_progress->phone }}</td>
    </tr>
    <tr>
        <th style="text-align: right;">Program Title:</th>
        <td>{{ $activity_progress->program_name }}</td>
    </tr>
    <tr>
        <th style="text-align: right;">Report Timeframe:</th>
        <td>{{ ucwords(str_replace('-', ' ', $activity_progress->schedule_interval)) }} {{ $activity_progress->schedule_interval == 'one-time' ? $activity_progress->scheduled_at: ''}}</td>
    </tr>

    @if($activity_progress->activity_type == 'activity-progress')
    <tr>
        <th style="text-align: right;">Report Type:</th>
        <td>{{ $activity_progress->fetch_report_type == 'all' ? 'All':'From '.date('M d Y', strtotime($activity_progress->fetch_start_date)).' to '. date('M d Y', strtotime($activity_progress->fetch_end_date)) }}</td>
    </tr>
    @endif

    <tr>
        <th style="text-align: right;">Requester Name: </th>
        <td>{{ Auth::user()->firstname.' '.Auth::user()->lastname }}</td>
    </tr>

    <tr>
        <th style="text-align: right;">Requester Email:</th>
        <td>{{ Auth::user()->email }}</td>
    </tr>
</table>