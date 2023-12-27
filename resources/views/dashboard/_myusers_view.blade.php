<table class="table table-earning data-table" id="table_id" data-total="{{count($records)}}">
    <thead>
    <tr>
        <th id="checkbox_sort">
            <input type="checkbox" class="checkallboxlist">
        </th>
        <th id="first_name_sort">First Name</th>
        <th id="last_name_sort">Last Name</th>
        <th id="email_sort">Email</th>
        <th id="phone_sort">Phone</th>
        <th id="role_sort">Role</th>
        <th id="status_sort">Status</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    @if(count($records) > 0)
        @foreach($records as $index => $student)
        <tr role="row" class="odd">
            <td class="checkbox_sort">
                <input type="checkbox" class="chklist" name="ids" value="{{ $student->id }}" >
            </td>
            <td class="first_name_sort">{{ $student->firstname }}</td>
            <td class="last_name_sort">{{ $student->lastname }}</td>
            <td class="email_sort">{{ $student->email }}</td>
            <td class="phone_sort">{{ $student->phone }}</td>
            <td class="role_sort">{{ $student->role_name }}</td>
            <td class="status_sort">{{ $student->status_label }}</td>
            <td>
                <div class="manage">
                    <a title="Edit" href="/dashboard/myusers/edit/{{ $student->id }}">
                        <i class="fa fa-id-card" aria-hidden="true"></i>
                    </a>
                    <a href="/dashboard/myusers/permission/{{ $student->id }}" class="lock">
                        <i class="fa fa-lock" aria-hidden="true"></i>
                    </a>
                </div>
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