
    @if(count($logs) > 0)
        @foreach($logs as $index => $val)        
        <tr role="row" class="odd" data-total="{{$total_record}}" data-id="{{ $val->_id }}">
            <td class="checkbox_sort">
                <input type="checkbox" class="chklist" name="ids" value="{{ $val->ids }}" >
            </td>
            <td class="what_deleted_sort">{{$val->breadcrumb}}</td>
            <td class="ip_address_sort">{{ $val->ip_address }}</td>
            <td class="who_deleted_sort">{{ $val->full_name }}</td>
            <td class="action_at_sort">{{ $val->created_at }}</td>
            <td class="data_sort">
                <button class="btn btn-primary btn-sm view-data" data-toggle="modal" data-target="#user-log-modal" data-id="{{ $val->ids }}"> View </button>
                <button class="btn btn-warning btn-sm revert-data" data-id="{{ $val->ids }}"> Revert </button>
            </td>
        </tr>
        @endforeach
    @else
        <tr id="no-record-found">
            <td colspan="{{ count($column_setting['column_position']) }}" style="text-align:center;">No more result.</td>
        </tr>
    @endif

{{--<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.24/jquery-ui.min.js"></script>
<script src="https://www.jqueryscript.net/demo/Simple-jQuery-Plugin-For-Draggable-Table-Columns-Dragtable/jquery.dragtable.js"></script>--}}

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
