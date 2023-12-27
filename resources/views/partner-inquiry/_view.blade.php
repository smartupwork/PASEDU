<?php
use \App\Models\UserAccess;
?>
<table class="table table-earning data-table" id="table_id">
    <thead>
    <tr>
        <th id="checkbox_sort"><input type="checkbox" class="checkallboxlist"></th>
        <th id="request_type_sort">Request Type</th>
        <th id="request_date_sort">Request Date</th>
        <th id="request_reason_sort">Request Reason</th>
        <th id="request_time_sort">Request Time</th>
        <th id="request_by_sort">Request By</th>
        <th id="request_message_sort">Request Message</th>
        <th id="request_status_sort">Request Status</th>
    </tr>
    </thead>
    <tbody>
    @if(count($records) > 0)
        @foreach($records as $index => $student)
        <tr role="row" class="odd" id="{{ $student->id }}">
            <td id="checkbox_sort">
                <input type="checkbox" class="chklist" name="ids" value="{{ $student->id }}" >
            </td>
            <td class="request_type_sort">{{$student->request_type}}</td>
            <td class="request_date_sort">{{ date(\App\Utility::DEFAULT_DATE_FORMAT, strtotime($student->added_date))}}</td>
            <td class="request_reason_sort">{{ $student->request_reason }}</td>
            <td class="request_time_sort">{{ date(\App\Utility::DEFAULT_TIME_FORMAT, strtotime($student->added_date)) }}</td>
            <td class="request_by_sort">{{ $student->firstname }} {{ $student->lastname }}</td>
            <td class="request_message_sort">{{ $student->message }}</td>
            <td class="request_status_sort">{{ $student->status }}</td>
        </tr>
        @endforeach
    @else
        <tr>
            <td colspan="{{ count($column_setting['column_position']) }}" style="text-align:center;">No Record Found.</td>
        </tr>
    @endif
    </tbody>
</table>
<script>
    $('.checkallboxlist').click(function () {
            if ($(this).is(':checked') == true) {
                $('.chklist').prop('checked', true);
            } else {
                $('.chklist').prop('checked', false);
            }
        });
    $(document).ready(function(){

        var visible_columns = {!! json_encode($column_setting['user_columns']) !!};
        //console.log(visible_columns);
        $.each(visible_columns, function(index, item){
            $('#'+item).hide();
            $('.'+item).hide();
        });
    });  
</script>