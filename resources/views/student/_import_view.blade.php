<?php
use \App\Models\UserAccess;
?>
<table class="table table-earning data-table" id="table_id" data-total="{{count($data)}}">
    <thead>
    <tr>
        <th id="checkbox_sort"><input type="checkbox" class="checkallboxlist"></th>
        <th id="file_name_sort">File Name</th>
        <th id="import_date_sort">Import Date</th>
        <th id="import_time_sort">Import Time</th>
        <th id="file_size_sort">File Size</th>
        <th id="processing_time_sort">Processing Time</th>
        <th id="imported_records_sort">Imported Records</th>
        <th id="warning_records_sort">Warning Record</th>
        <th id="skipped_records_sort">Skipped Records</th>
        <th id="imported_by_sort">Imported By</th>
    </tr>
    </thead>
    <tbody>
    @if(count($data) > 0)
        @foreach($data as $index => $student)
        
        <tr role="row" class="odd">
            <td id="checkbox_sort">
                <input type="checkbox" class="chklist" name="ids" value="{{ $student['mid'] }}" >
            </td>
            <td class="file_name_sort">
            <a href="{{ route('download-import-file', $student['mid']) }}" style="width:150px !important;font-size:14px;text-decoration: underline;">{{ $student['file'] }}</a>
            </td>
            <td class="import_date_sort">{{ $student['date'] }}</td>
            <td class="import_time_sort">{{ $student['import_time'] }}</td>
            <td class="file_size_sort">{{ $student['file_size'] }}</td>
            <td class="processing_time_sort">{{ $student['processing_time'] }}</td>
            <td class="imported_records_sort">{{ $student['imported_records'] }}</td>
            <td class="warning_records_sort">
                @if($student['records_imported_warning'] > 0)
                    <a href="{{ route('import-skipped-record', ['list_type' => 'warning', 'id' => $student['mid']]) }}" style="text-decoration: underline; color: #007bff">{{ $student['records_imported_warning'] }}</a>
                @else
                    {{ $student['records_imported_warning'] }}
                @endif
            </td>
            <td class="skipped_records_sort">
                @if($student['records_skiped'] > 0)
                    <a href="{{ route('import-skipped-record', ['list_type' => 'skipped', 'id' => $student['mid']]) }}" style="text-decoration: underline; color: #007bff">{{ $student['records_skiped'] }}</a>
                @else
                    {{ $student['records_skiped'] }}
                @endif
            </td>
            <td class="imported_by_sort">{{ $student['added_by'] }}</td>
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
    $('.checkallboxlist').click(function () {
        if ($(this).is(':checked') == true) {
            $('.chklist').prop('checked', true);
        } else {
            $('.chklist').prop('checked', false);
        }
    });     
</script>