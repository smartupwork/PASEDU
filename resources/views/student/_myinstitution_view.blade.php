<?php
use \App\Models\UserAccess;
?>
<table class="table table-earning data-table" id="table_id" data-total="{{ count($data) }}">
    <thead>
    <tr>
        <th id="request_sort">Request</th>
        <th id="request_date_sort">Request Date</th>
        <th id="request_time_sort">Request Time</th>
        <th id="request_by_sort">Request By</th>
        <th id="name_sort">Name</th>
        <th id="program_sort">Program</th>
        <th id="username_sort">Username</th>
        <th id="purpose_sort">Purpose</th>
        <th id="desired_completion_date_sort">Desired Completion Date</th>
        <th id="meeting_proposed_date_sort">Meeting Proposed Date</th>
        <th id="request_status_sort">Request Status</th>
    </tr>
    </thead>
    <tbody>
    @if(count($data) > 0)
        @foreach($data as $index => $student)
        <tr role="row" class="odd">
            <td class="request_sort">{{$student['request_type']}}</td>
            <td class="request_date_sort">{{ $student['request_date'] }}</td>
            <td class="request_time_sort">{{ $student['request_time'] }}</td>
            <td class="request_by_sort">{{ $student['requested_by'] }}</td>
            <td class="name_sort">{{ $student['name'] }}</td>
            <td class="program_sort">{{ $student['program_name'] }}</td>
            <td class="username_sort">{{ $student['username'] }}</td>
            <td class="purpose_sort">{{ $student['purpose'] }}</td>
            <td class="desired_completion_date_sort">{{ $student['desired_completion_date'] }}</td>
            <td class="meeting_proposed_date_sort">{{ $student['meeting_proposed_date'] }}</td>
            <td class="request_status_sort">
                <input type="hidden" name="ids[]" value="{{ $student['id'] }}" />
                <input type="hidden" name="old_status[]" value="{{ $student['status'] }}" />
                <select name="status[]" class="form-control" aria-invalid="{{ $student['status'] }}">
                    <option @if(isset($student)){{ $student['status'] == '1'? 'selected':'' }}@endif value="1">Open</option>
                    <option @if(isset($student)){{ $student['status'] == '2'? 'selected':'' }}@endif value="2">Cancelled</option>
                    <option @if(isset($student)){{ $student['status'] == '3'? 'selected':'' }}@endif value="3">Completed</option>
                </select>
            </td>
        </tr>
        @endforeach
    @else
        <tr>
            <td colspan="{{ count($column_setting['column_position']) }}" style="text-align:center;">No Record Found.</td>
        </tr>
    @endif
    </tbody>
</table>
{{--<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.24/jquery-ui.min.js"></script>
<script src="https://www.jqueryscript.net/demo/Simple-jQuery-Plugin-For-Draggable-Table-Columns-Dragtable/jquery.dragtable.js"></script>--}}

<script>

    $(document).ready(function(){

        var visible_columns = {!! json_encode($column_setting['user_columns']) !!};
        //console.log(visible_columns);
        $.each(visible_columns, function(index, item){
            $('#'+item).hide();
            $('.'+item).hide();
        });
    });        
</script>