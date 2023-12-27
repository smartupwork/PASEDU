<?php
use \App\Models\UserAccess;
?>
<table class="table table-earning data-table" id="table_id" data-total="{{count($result)}}">
    <thead>
    <tr>
        <th id="program_sort">Program</th>
        <th id="code_sort">Code</th>
        <th id="hours_sort">Hours</th>
        <th id="srp_sort">SRP</th>
        <th id="wholesale_sort">Wholesale</th>
        <th id="type_sort">Type</th>
        @if(UserAccess::hasAccess(UserAccess::CATALOG_MANAGEMENT_ACCESS, 'add'))
        <th id="status_sort">Status</th>
        @endif
        <th id="description_sort">Description</th>
        <th id="exam_included_sort">Exam Included</th>
        @if(UserAccess::hasAccess(UserAccess::CATALOG_MANAGEMENT_ACCESS, 'add'))
        <th>Action</th>
        @endif
    </tr>
    </thead>
    <tbody>
    @if(count($result) > 0)
        @foreach($result as $index => $student)
        <tr role="row" class="odd">
            <td class="program_sort">{{$student->name}}</td>
            <td class="code_sort">{{$student->code}}</td>
            <td class="hours_sort">{{$student->hours}}</td>
            <td class="srp_sort">{{ $student->unite_price }}</td>
            <td class="wholesale_sort">{{ $student->retail_wholesale }}</td>
            <td class="type_sort">{{ $student->program_type }}</td>
            @if(UserAccess::hasAccess(UserAccess::CATALOG_MANAGEMENT_ACCESS, 'add'))
                @if(in_array($student->_id, $available_programs))
                    <td class="status_sort text-success">Active</td>
                @else
                    <td class="status_sort text-danger">Inactive</td>
                @endif
            @endif
            @if($student->description != '')
                <td class="description_sort" title="{{ $student->description }}">{{ substr($student->description, 0 ,55) }}....</td>
            @else
                <td class="description_sort"></td>
            @endif
            <td class="exam_included_sort">{{ $student->certification_included }}</td>
            @if(UserAccess::hasAccess(UserAccess::CATALOG_MANAGEMENT_ACCESS, 'add'))
            <td>
                @if(in_array($student->_id, $available_programs))
                    <button type="button" class="btn btn-danger btn-sm change-status" data-id="{{ $student->id }}" data-action="remove" data-zoho_id="{{ $student->zoho_id }}" data-list-price="{{ $student->unite_price }}"  data-program-name="{{ $student->name }}">x</button>
                @else
                    <button type="button" class="btn btn-success btn-sm change-status" data-id="{{ $student->id }}"  data-zoho_id="{{ $student->zoho_id }}"  data-action="add" data-zoho_id="{{ $student->zoho_id }}" data-list-price="{{ $student->unite_price }}" data-program-name="{{ $student->name }}">+</button>
                @endif
            </td>
            @endif
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