
    @if(count($records) > 0)
        @foreach($records as $index => $student)
        <tr role="row" class="odd" data-total="{{$total_record}}" data-id="{{ $student->_id }}">
            <td class="checkbox_sort">
                <input type="checkbox" class="chklist" name="ids" value="{{ $student->id }}" >    
            </td>
            <td class="to_sort">{{ $student->to_email }}</td>
            <td class="subject_sort">{{ $student->subject }}</td>
            <td class="date_sort">{{ $student->added_date }}</td>
            <td>
                <a href="/dashboard/systememaillogs/edit/{{ $student->id }}">
                    <i class="fa fa-edit" aria-hidden="true"></i>
                </a>
            </td>
        </tr>
        @endforeach
    @else
        <tr id="no-record-found">
            <td colspan="5" style="text-align:center;">No more result.</td>
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
