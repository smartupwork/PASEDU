<div class="table-responsive m-b-20">
    <table style="width:100%" class="table table-earning data-table">
        <tr style="font-size: 14px;">
            <th>S.No</th>
            <th>Request</th>
            <th>Request Date</th>
            <th>Request Time</th>
            <th>Request By</th>
            <th>Name</th>
            <th>Program</th>
            <th>Username</th>
            <th>Purpose</th>
            <th>Desired Completion Date</th>
            <th>Meeting Proposed Date	</th>
            <th>Status</th>
        </tr>
        @foreach($data as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->request_type_label }}</td>
                <td>{{ date(\App\Utility::DEFAULT_DATE_FORMAT, strtotime($item->requested_date)) }}</td>
                <td>{{ date(\App\Utility::DEFAULT_TIME_FORMAT_INSTITUTION, strtotime($item->requested_date)) }}</td>
                <td>{{ $item->requested_by }}</td>
                <td>{{ ($item->request_type == 1 ? $item->subject: '-') }}</td>
                <td>{{ $item->program_name ? $item->program_name:'-' }}</td>
                <td>{{ $item->username ? $item->username :'-'}}</td>
                <td>{{ $item->purpose ? $item->purpose: '-' }}</td>
                <td>{{ $item->desired_completion_date ? $item->desired_completion_date:'-' }}</td>
                <td>{{ $item->meeting_proposed_date ? $item->meeting_proposed_date: '-' }}</td>
                <td>{{ $item->status_label }}</td>
            </tr>
        @endforeach
    </table>
</div>
