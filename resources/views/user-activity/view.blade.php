@if(count($logs) > 0)
    @foreach($logs as $index => $student)
    <tr role="row" class="odd" data-total="{{$total_record}}" data-id="{{$student->_id}}">
        <td class="action_sort">{{$student->action}}</td>
        <td class="log_type_sort">{{ $student->breadcrumb }}</td>
        <td class="ip_address_sort">{{ $student->ip_address }}</td>
        <td class="action_by_sort">{{ $student->full_name }}</td>
        <td class="action_at_sort">{{ $student->created_at }}</td>
        <td class="data_sort">
            @if($student->action !== "Login")
                <button class="btn btn-primary btn-sm view-data" data-toggle="modal" data-target="#user-log-modal" data-id="{{ $student->id }}"> View </button>
            @endif
        </td>
    </tr>
    @endforeach
@else
    <tr id="no-record-found">
        <td colspan="{{ count($column_setting['column_position']) }}" style="text-align:center;">No more result.</td>
    </tr>
@endif

{{--
<script>
    $(document).ready(function(){

        var visible_columns = {!! json_encode($column_setting['user_columns']) !!};
        //console.log(visible_columns);
        $.each(visible_columns, function(index, item){
            $('#'+item).hide();
            $('.'+item).hide();
        });
    });     
</script>--}}
