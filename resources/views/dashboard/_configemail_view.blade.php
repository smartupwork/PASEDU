<table class="table table-earning data-table" id="table_id" data-total="{{count($records)}}">
    <thead>
    <tr>
        <th id="type_sort">Type</th>
        <th id="subject_sort">Subject</th>
        <th id="description_sort">Description</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    @if(count($records) > 0)
        @foreach($records as $index => $student)
        <tr role="row" class="odd">
            <td class="type_sort">{{ $student->type }}</td>
            <td class="subject_sort">{{ $student->subject }}</td>
            <td class="description_sort">{{ $student->message }}</td>
            <td>
                <a href="/dashboard/configemail/edit/{{ $student->id }}">
                    <i class="fa fa-edit" aria-hidden="true"></i>
                </a>
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