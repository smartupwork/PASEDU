<div class="table-responsive m-b-20">
    <table style="width:100%" class="table table-earning data-table">
        <tr style="font-size: 14px;">
            <th>S.No</th>
            <th>Subject</th>
            <th>Status</th>
            <th>Grand Total</th>
            <th>Start Date</th>
            <th>Program Name</th>
            <th>Completion Date</th>
            <th>End Date</th>
            <th>Final Grade</th>
            <th>Username</th>
            {{--<th>enrollment_created_at</th>
            <th>enrollment_updated_at</th>--}}
        </tr>
        @if(isset($data['new_data']) && count($data['new_data']) > 0)
            @foreach($data['new_data'] as $index => $student)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td style="text-align: left;">{{ $student['subject'] }}</td>
                <td style="text-align: left;">{{ $student['status'] }}</td>
                <td style="text-align: left;">{{ $student['grand_total'] }}</td>
                <td style="text-align: left;">{{ date(\App\Utility::DEFAULT_DATE_FORMAT, strtotime($student['start_date'])) }}</td>
                <td style="text-align: left;">{{ $student['program_name'] }}</td>
                <td style="text-align: left;">{{ !empty($student['completion_date']) ? date(\App\Utility::DEFAULT_DATE_FORMAT, strtotime($student['completion_date'])):'-' }}</td>
                <td style="text-align: left;">{{ !empty($student['end_date']) ? date(\App\Utility::DEFAULT_DATE_FORMAT, strtotime($student['end_date'])):'-' }}</td>
                <td style="text-align: left;">{{ $student['final_grade'] }}</td>
                <td style="text-align: left;">{{ $student['username'] }}</td>
                {{--<td style="text-align: left;">{{ $student['status'] }}</td>--}}
            </tr>
            @endforeach
        @endif
    </table>
</div>
