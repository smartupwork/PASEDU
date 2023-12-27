<?php
use \App\Models\UserAccess;
?>
    @if(count($result) > 0)
        @foreach($result as $index => $student)
        <tr role="row" class="odd {{ $student->parent }}" id="{{ $student->id }}">
            <td id="load_more_sort"></td>
            <td class="first_name_sort sorting">{{$student->first_name}}</td>
            <td class="last_name_sort sorting">{{$student->last_name}}</td>
            <td class="email_sort sorting">{{ $student->email }}</td>
            <td class="program_sort sorting">{{ $student->program_name }}</td>
            <td class="status_sort sorting">{{ $student->payment_amount }}</td>
            <td class="grand_total_sort sorting">{{ $student->price_paid }}</td>
            <td class="start_date_sort sorting">{{ $student->payment_type }}</td>
            <td class="progress_sort sorting">{{ $student->start_date }}</td>
            <td class="program_sort sorting">{{ $student->end_date }}</td>
            <td class="completion_date_sort sorting">{{ $student->phone }}</td>
            <td class="end_date_sort sorting">{{ $student->street }}</td>
            <td class="final_grade_sort sorting">{{ $student->city }}</td>
            <td class="user_name_sort sorting">{{ $student->state }}</td>
            <td class="user_name_sort sorting">{{ $student->zip }}</td>
            <td class="user_name_sort sorting">{{ $student->country }}</td>
            @if(UserAccess::hasAccess(UserAccess::STUDENT_MANAGEMENT_ACCESS, 'view'))
                <td>
                    <a href="{{ route("student-detail", $student->id) }}" class="user-primary">
                        <i class="fa fa-id-card" aria-hidden="true"></i>
                    </a>
                </td>
            @endif
        </tr>
        @endforeach
    @else
        <tr>
            <td colspan="{{ count($column_setting['column_position']) }}" style="text-align:center;">No Record Found.</td>
        </tr>
    @endif
<script>
    $(document).ready(function(){
        var visible_columns = {!! json_encode($column_setting['user_columns']) !!};
        //console.log(visible_columns);
        $.each(visible_columns, function(index, item){
            $('.'+item).hide();
        });
    });
</script>