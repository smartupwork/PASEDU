<?php
use \App\Models\UserAccess;

?>
@extends('layout.main')
@section('content')

    <div class="section__content">
        <div class="container-fluid">
            <h2 class="page-titel m-b-20">Student Dashboard</h2>
            <div class="row adv-search-row">
                <div class="col-md-5">
                    <div class="form-group">
                        <div class="input-group">
                            <input class="form-control py-2 border-right-0 border" type="text" value=""
                                   placeholder="Search" onkeyup="loadcommandfun();" id="q" name="q" autocomplete="off">
                            <span class="input-group-append">
                                            <button class="btn btn-outline-secondary border-left-0 border"
                                                    type="button">
                                                <i class="fa fa-search"></i>
                                            </button>
                                        </span>
                            <div class="input-group-addon adv-search" data-toggle="collapse"
                                 href="#multiCollapseExample1">
                                <i class="fa fa-angle-double-down" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-7 text-right pt-3">
                    <button type="button" data-toggle="modal" data-target="#ColumnSetting" class="btn btn-icon1">
                        <i class="fa fa-cog" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <div class="row collapse multi-collapse" id="multiCollapseExample1">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <strong>Advance Search</strong>
                        </div>
                        <div class="card-body card-block m-3">
                            <form action="" method="post" class="form-horizontal">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="first-Name" class=" form-control-label">Subject</label>
                                            <input type="text" id="subject" name="subject" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label for="last-name" class=" form-control-label">Status</label>
                                            <input type="text" id="status" name="status" class="form-control">
                                        </div>

                                    </div>
                                    <div class="col-md-6">
                                        {{--<div class="form-group">
                                            <label for="status" class=" form-control-label">Status</label>
                                            <select name="status" id="status" class="form-control">
                                                <option value="">Please select</option>
                                                @foreach(\App\Models\Student::getStatus() as $id => $label)
                                                    <option value="{{$id}}">{{$label}}</option>
                                                @endforeach
                                            </select>
                                        </div>--}}
                                        <div class="form-group">
                                            <label for="program" class=" form-control-label">Program</label>
                                            <input type="text" id="program" name="program" class="form-control">
                                        </div>

                                        <div class="form-label-group">
                                            <label for="example-date-input" class="col-form-label">Username</label>
                                            <input class="form-control" type="text" id="username" id="username">
                                        </div>

                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="card-footer text-right">
                            <button type="button" onclick="loadcommandfun();" class="btn btn-primary btn-sm">
                                <i class="fa fa-search"></i> Search
                            </button>
                            <button type="button" onclick="rstfun();" class="btn btn-secondary btn-sm float-right ml-1">
                                Reset
                            </button>
                        </div>
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-12"><!-- id="listid"-->
                    <div class="table-responsive m-b-20" id="listid">
                        
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="text-left" id="total-records"></p>
                        </div>
                        <div class="col-md-8 text-right">
                            @if(UserAccess::hasAccess(UserAccess::STUDENT_ENROLLMENT_ACCESS, 'download'))
                            <button type="button" onclick="exporttoexcel();" class="btn btn-secondary btn-sm">
                                <i class="fa fa-file-excel" aria-hidden="true"></i> Export to Excel
                            </button>
                            <button type="button" onclick="exporttopdf();" class="btn btn-secondary btn-sm">
                                <i class="fa fa-file-pdf"></i> Export to PDF
                            </button>
                            {{--<button type="button" onclick="deleteStudent();" class="btn btn-danger btn-sm">
                                <i class="fa fa-times"></i> Delete Row(s)
                            </button>--}}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @include('layout.dashboard.footer')
        </div>
    </div>
    <div class="modal fade" id="ColumnSetting" tabindex="-1" role="dialog" aria-labelledby="ColumnSettingLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form name="frmid" id="frmid" action="{{ route('leads-view-header-update') }}" method="post">
                <input type="hidden" name="module" value="student_dashboard">
                {{ csrf_field() }}
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ColumnSettingLabel">Columns</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-start modal-search">       
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1"><i class="icon-search"></i></span>
                            </div>
                            <input id="txt_all" type="text" class="form-control" placeholder="Search">
                        </div>
                    </div>
                    <div class="table-responsive table-scroll">
                      <table id="tblLocations" class="table table-striped table-bordered modal-table">
                          <thead>
                          <tr>
                            <th width="60px" class="text-center">
                                <input type="checkbox" class="checkallbox" value="all">
                            </th>
                            <th>All</th>
                          </tr>
                          </thead>
                          <tbody>
                          @foreach($column_setting['column_position'] as $column => $index)
                              @php
                                  $column_name = $column == 'subject_sort' ? 'name_sort':$column;
                                      @endphp
                          <tr>
                            <td class="text-center">
                                <input type="hidden" name="position[]" value="{{$column}}">
                                <input type="checkbox" class="chk" name="is_visible[]" value="{{$column}}" {{ !in_array($column, $column_setting['user_columns']) ? 'checked':''}}>
                            </td>
                            <td>{{ ucwords(str_replace('sort',' ', str_replace('_',' ', $column_name))) }}</td>
                          </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                </div>
                <div class="modal-footer">
                <div class="row">
                    <div class="col-md-12 text-right">
                    <button type="submit" name="btnAdd" id="btnAdd" class="btn btn-primary">Save changes</button>
                    <button type="button" class="btn btn-inverse-secondary btn-fw" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
                </div>
            </form>
          </div>
        </div>
    </div>
        <div class="modal fade" id="mediumModal" tabindex="-1" role="dialog" aria-labelledby="mediumModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <form name="frmname" id="requestfrm" method="post" action="{{ route('student-activity-report-save')}}">
            <div class="modal-content">

            </div>
        </form>
    </div>
    </div>
                
    

@section('myCssFiles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/css/bootstrap-datepicker.css" rel="stylesheet" media="all">
    <style type="text/css">
        #tblLocations tbody tr {
          cursor: move;
        }
    
    
        table.dataTable thead .sorting,table.dataTable thead .sorting_asc,table.dataTable thead .sorting_desc,table.dataTable thead .sorting_asc_disabled,table.dataTable thead .sorting_desc_disabled{
            background-repeat:no-repeat;
            background-position:center right
        }
    
        table.dataTable thead .sorting{
            background-image:url("../images/sort_both.png")
        }
        table.dataTable thead .sorting_asc{
            background-image:url("../images/sort_asc.png")
        }
        table.dataTable thead .sorting_desc{
            background-image:url("../images/sort_desc.png")
        }
        /*table.dataTable thead .sorting_asc_disabled{
            background-image:url("../images/sort_asc_disabled.png")
        }
        table.dataTable thead .sorting_desc_disabled{
            background-image:url("../images/sort_desc_disabled.png")
        }*/
    
    </style>
@stop

@section('myJsFile')
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.24/themes/smoothness/jquery-ui.css" />
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.24/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/js/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://www.jqueryscript.net/demo/Simple-jQuery-Plugin-For-Draggable-Table-Columns-Dragtable/jquery.dragtable.js"></script>
    <script src="{{$CDN_URL}}js/jquery.form.js"></script>
    <script>
        $(document).on('click', '.view-data', function () {
            $("#mediumModal").find('.modal-content').html('<div style="text-align: center;"><img src="/images/loader.svg"></div>');
            $.ajax({
                type: "GET",
                url: "{{route('student-popup')}}",
                data: {
                    enrollment_id: $(this).data('id'),
                    student_name: $(this).data('student-name'),
                    activity_type: $(this).data('activity-type')
                },
                //dataType : 'json',
                cache: true,
                success: function(data){
                    //$("#resultset").html(data.html);
                    //$("#resultset").html(data);
                    $("#mediumModal").find('.modal-content').html(data);

                    $(function() {

                        var date = new Date();
                        var c = date.getFullYear() + 10;
                        $("#fetch_start_date, #fetch_end_date, #scheduled_at").datepicker({
                            //dateFormat: "yy-mm-dd",
                            //defaultViewDate: '2022-04-22',
                            changeMonth: true,
                            changeYear: true,
                            autoclose: true,
                            showOn: "button",
                            buttonText: '<i class="fa fa-calendar" aria-hidden="true"></i>',
                            yearRange : '1940:'+c,
                        });
                    });

                }
            });
        });

        $(document).on('click', '#manually-report-yes', function () {
            $('#manually-report-container').hide();
            $('#scheduler-form-container').show();
            $('.modal-footer').show();
        });

        $(document).ready(function(){
            $("#txt_all").keyup(function(){        
                // Retrieve the input field text and reset the count to zero
                var filter = $(this).val();        
                // Loop through the comment list
                $("#tblLocations tbody tr").each(function(){        
                    // If the list item does not contain the text phrase fade it out
                    if ($(this).text().search(new RegExp(filter, "i")) < 0) {
                        $(this).fadeOut();        
                        // Show the list item if the phrase matches and increase the count by 1
                    } else {
                        $(this).show();
                    }
                });
            });
        });
        $('#tblLocations tbody').sortable();
        $(document).ready(function(){
          $(".sort-by").click(function(){
            $(this).toggleClass('sort-asc');
            // alert("hello");
          });
        });
        $('#frmid').ajaxForm({
                beforeSubmit: function() {
                    $("#submit-btn").html('Processing...').attr('disabled', true);
                },
                dataType: 'json',
                success: function(data) {
                    if(data.status == "success"){
                        alert(data.msg);
                        window.location.reload();
                    }else{
                        alert(data.msg);
                    }
                },
                error: function(xhr){
                    if(xhr.status === 419){
                        window.location.reload();
                    }else{
                        $("#submit-btn").html('Save').attr('disabled', false);
                        alert(xhr.responseJSON.message);
                    }
                }
            });
        function rstfun() {
            $("#q").val('');
            $("#fname").val('');
            $("#lname").val('');
            $("#program").val('');
            $("#status").val('');
            $("#sdate").val('');
            $("#type").val('');
            $("#username").val('');
            $("#subject").val('');
            loadcommandfun();
        }

        function exporttopdf() {
            var q = $("#q").val();
            window.location.href = '{{ route("enrollment-export-pdf") }}?q=' + q + '&subject=' + $("#subject").val() + '&status=' + $("#status").val() + '&program=' + $("#program").val() + '&username=' + $("#username").val();
        }

        function exporttoexcel() {
            var q = $("#q").val();
            window.location.href = '{{ route("enrollment-export-excel") }}?q=' + q + '&subject=' + $("#subject").val() + '&status=' + $("#status").val() + '&program=' + $("#program").val() + '&username=' + $("#username").val();
        }

        var currentRequest = null;
        var timer;

        function loadcommandfun() {
            clearTimeout(timer) // clear the request from the previous event
            timer = setTimeout(function() {
                var q = $('#q').val();
                currentRequest = $.ajax({
                    type: "GET",
                    url: "{{ route('enrollment-search') }}",
                    cache: true,
                    data: 'q=' + q + '&subject=' + $("#subject").val() + '&status=' + $("#status").val() + '&program=' + $("#program").val() + '&username=' + $("#username").val(),
                    beforeSend: function() {
                        if(currentRequest != null) {
                            currentRequest.abort();
                        }
                    },
                    success: function (data) {
                        $("#listid").html(data);

                        $('#table_id').dragtable({
                            dragaccept:'.accept',
                            restoreState: {!! json_encode($column_setting['column_position']) !!}
                        });

                        $('#total-records').text( 'Total Records: '+$('#table_id').data('total'));

                        $('#table_id').DataTable({
                            searching: false,
                            paging: false,
                            info: false,
                            //ordering: true,
                            columnDefs: [
                                //{orderable: false, targets: 0},
                                {orderable: true, className: 'reorder', targets: '_all'}
                            ]
                        });
                    }
                });
            }, 300);
        }

        window.onload = loadcommandfun();
        
    $('#requestfrmpopup').ajaxForm({
        beforeSubmit: function() {
            $("#btnAdd").html('Processing...')
        },
        dataType: 'json',
        success: function(data) {
            $("#btnAdd").html('Ok');
            if(data.status == "success"){
                alert(data.msg);
                window.location.reload();
            }else{
                alert(data.msg);
            }
        },
        error: function(xhr){
            if(xhr.status === 419){
                window.location.reload();
            }else{
                $("#btnAdd").html('Ok')
                alert(xhr.responseJSON.message);
            }
        }
    });


    $('#requestfrm').ajaxForm({
        beforeSubmit: function() {
            $("#btnAdd").html('Processing...')
        },
        dataType: 'json',
        success: function(data) {
            $("#btnAdd").html('Submit');
            if(data.status == "success"){
                if(data.url !== ''){
                    window.open(data.url);
                }else{
                    alert(data.message);
                }
                //$('#mediumModal').modal('hide');
                window.location.reload();
            }else{
                alert(data.message);
            }
        },
        error: function(xhr){
            if(xhr.status === 419){
                window.location.reload();
            }else{
                $("#btnAdd").html('Submit');
                alert(xhr.responseJSON.message);
            }
        }
    });
    $(document).on('click', '.date_of_birth', function(){
        $(this).text();
    });

    $(document).on('click', '.schedule_interval', function(){
        if($(this).val() === 'one-time'){
            $('#scheduled_at').show();
            $('#is-recurring-container').hide();
        }else {
            $('#scheduled_at').hide();
            $('#is-recurring-container').show();
        }
    });
    $(document).on('click', '.report_type', function(){
        if($(this).val() === 'generate-report'){
            $('#schedule_interval_container').hide();
            $('#date-range-container').show();
        }else if($(this).val() === 'schedule-report'){
            $('#schedule_interval_container').show();
            $('#date-range-container').hide();
        }
    });

    $(document).on('change', '.fetch_report_type', function(){
        if($(this).is(":checked")){
            $('#fetch_report_date_range_container').show();
        }else {
            $('#fetch_report_date_range_container').hide();
        }
    });

    $(document).on('click', '.social_security_number', function(){
        $(this).text($(this).data('title'));
    });
</script>
    @stop
@stop

