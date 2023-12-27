<?php
use \App\Models\UserAccess;
?>
<table class="table table-earning data-table" id="table_id" data-total="{{count($result)}}">
    <thead>
    <tr>
        <th id="subject_sort">Name</th>
        <th id="date_of_birth_sort">Date Of Birth</th>
        <th id="social_security_number_sort">Social Security Number</th>
        <th id="partner_name_sort">Partner Name</th>
        <th id="status_sort">Status</th>
        <th id="grand_total_sort">Grand Total</th>
        <th id="start_date_sort">Start Date</th>
        <th id="activity_progress_sort">Program Progress</th>
        <th id="activity_log_sort">Access/Activity Log</th>
        <th id="program_sort">Program</th>
        <th id="completion_date_sort">Completion Date</th>
        <th id="end_date_sort">End Date</th>
        <th id="final_grade_sort">Final Grade</th>
        <th id="user_name_sort">User Name</th>
    </tr>
    </thead>
    <tbody>
    @if(count($result) > 0)
        @foreach($result as $index => $student)
        <tr role="row" class="odd">
            <td class="subject_sort">{{$student->subject}}</td>
            <td class="date_of_birth_sort" data-title="{{$student->date_of_birth}}" id="dobdataid{{ $index }}">
                @if(!empty($student->date_of_birth))
                <span id="dobspnid{{ $index }}">****** </span><i id="dobicoid{{ $index }}" onclick="displayhide({{ $index }},'dob','s');" class="fa fa-eye" style="margin-left:2px;"></i>
                @else
                    -
                @endif
            </td>
            <td class="social_security_number_sort" data-title="{{$student->social_security_number}}" id="slsndataid{{ $index }}">
                @if(!empty($student->social_security_number))
                <span id="slsnspnid{{ $index }}">****** </span><i id="slsnicoid{{ $index }}" onclick="displayhide({{ $index }},'slsn','s');" class="fa fa-eye" style="margin-left:2px;"></i>
                @else
                    -
                @endif
            </td>
            <td class="partner_name_sort">{{ $student->partner_name }}</td>
            <td class="status_sort">{{ $student->status }}</td>
            <td class="grand_total_sort">{{ $student->grand_total }}</td>
            <td class="start_date_sort">{{ $student->start_date }}</td>
            <td class="activity_progress_sort">
                @if(UserAccess::hasAccess(UserAccess::STUDENT_MANAGEMENT_ACCESS, 'add'))
                    <button  data-target="#mediumModal" data-id="{{ $student->id }}" data-student-name="{{ $student->subject }}" data-activity-type="activity-log" data-toggle="modal" class="btn btn-primary btn-sm view-data" type="button">Request</button>
                @endif
            </td>
            <td class="activity_log_sort">
                @if(UserAccess::hasAccess(UserAccess::STUDENT_MANAGEMENT_ACCESS, 'add'))
                    <button  data-target="#mediumModal" data-id="{{ $student->id }}" data-student-name="{{ $student->subject }}" data-activity-type="activity-progress" data-toggle="modal" class="btn btn-primary btn-sm view-data" type="button">Request</button>
                @endif
            </td>
            <td class="program_sort">{{ $student->program_name }}</td>
            <td class="completion_date_sort">{{ $student->completion_date }}</td>
            <td class="end_date_sort">{{ $student->end_date }}</td>
            <td class="final_grade_sort">{{ $student->final_grade }}</td>
            <td class="user_name_sort">{{ $student->username }}</td>
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

        function hidefun(t){//alert(t);
            if(t == 'Yes'){
                $("#occurrence1").css('display','block');
            }
            if(t == 'No'){
                $("#occurrence1").css('display','none');
            }
        }
    $(document).ready(function(){

        var visible_columns = {!! json_encode($column_setting['user_columns']) !!};
        //console.log(visible_columns);
        $.each(visible_columns, function(index, item){
            $('#'+item).hide();
            $('.'+item).hide();
        });
    });
    function displayhide(cnt, type){
        if(type == 'dob'){
            if($("#dobicoid"+cnt).attr('class') == 'fa fa-eye'){
                $("#dobspnid"+cnt).html($("#dobdataid"+cnt).data('title'));
                $("#dobicoid"+cnt).removeClass("fa fa-eye");
                $("#dobicoid"+cnt).addClass("fa fa-eye-slash");
            }else{
                $("#dobspnid"+cnt).html('******');
                $("#dobicoid"+cnt).removeClass("fa fa-eye-slash");
                $("#dobicoid"+cnt).addClass("fa fa-eye");
            }            
        }
        if(type == 'slsn'){
            if($("#slsnicoid"+cnt).attr('class') == 'fa fa-eye'){
                $("#slsnspnid"+cnt).html($("#slsndataid"+cnt).data('title'));
                $("#slsnicoid"+cnt).removeClass("fa fa-eye");
                $("#slsnicoid"+cnt).addClass("fa fa-eye-slash");
            }else{
                $("#slsnspnid"+cnt).html('******');
                $("#slsnicoid"+cnt).removeClass("fa fa-eye-slash");
                $("#slsnicoid"+cnt).addClass("fa fa-eye");
            }            
        }
    }
    

        
</script>