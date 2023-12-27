<?php
use \App\Models\UserAccess;

?>
@extends('layout.main')
@section('content')

    <div class="section__content">
        <div class="container-fluid">
            <h2 class="page-titel m-b-20">Student Enrollment</h2>
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
                <div class="col-md-7 text-right pt-3 pb-2">
                    <button type="button" data-toggle="modal" data-target="#ColumnSetting" class="btn btn-icon1">
                        <i class="fa fa-cog" aria-hidden="true"></i>
                    </button>
                    @if(UserAccess::hasAccess(UserAccess::STUDENT_MANAGEMENT_ACCESS, 'add'))
                    <a href="{{ route('student-add') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-user-plus"></i> Add Student Enrollment</a>
                    @endif
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
                                            <label for="first-Name" class=" form-control-label">First Name</label>
                                            <input type="text" id="fname" name="fname" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label for="last-name" class=" form-control-label">Last Name</label>
                                            <input type="text" id="lname" name="lname" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label for="program" class=" form-control-label">Program</label>
                                            <input type="text" id="program" name="program" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">

                                        <div class="form-label-group">
                                            <label for="example-date-input" class="col-form-label">Start Date</label>
                                            <input class="form-control" type="text" id="sdate" id="sdate">
                                        </div>

                                        <div class="form-group">
                                            <label for="type" class=" form-control-label">Payment Type</label>
                                            <select name="type" id="type" class="form-control">
                                                <option value="">Please select</option>
                                                @foreach(\App\Models\Student::getPaymentType() as $payment_type)
                                                    <option value="{{$payment_type['actual_value']}}">{{$payment_type['display_value']}}</option>
                                                @endforeach
                                            </select>
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
                <div class="col-md-12">
                    <div class="table-responsive m-b-20" id="listid">

                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="text-left" id="total-records"></p>
                        </div>
                        <div class="col-md-8 text-right">
                            @if(UserAccess::hasAccess(UserAccess::STUDENT_MANAGEMENT_ACCESS, 'download'))
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
                <input type="hidden" name="module" value="student_enrollment">
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
                          <tr style="{{ $column ==  'load_more_sort' ? 'display:none;':'block'}};">
                            <td class="text-center">
                                <input type="hidden" name="position[]" value="{{$column}}">
                                <input type="checkbox" class="{{ $column ==  'load_more_sort' ? 'chk1':'chk'}}" name="is_visible[]" value="{{$column}}" {{ !in_array($column, $column_setting['user_columns']) ? 'checked':''}}>
                            </td>
                            <td>{{ ucwords(str_replace('sort',' ', str_replace('_',' ', $column))) }}</td>
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
@section('myCssFiles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/css/bootstrap-datepicker.css" rel="stylesheet" media="all">
    <style type="text/css">
        #tblLocations tbody tr {
          cursor: move;
        }

        #tblLocations .unsortable{
            background: #999;
            opacity:.5;
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

{{--<script src="https://unpkg.com/bootstrap-table@1.19.1/dist/bootstrap-table.min.js"></script>
<script src="https://unpkg.com/bootstrap-table@1.19.1/dist/extensions/reorder-columns/bootstrap-table-reorder-columns.min.js"></script>--}}
    <script>
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
        $('#tblLocations tbody').sortable({
            //items: "li:not(.unsortable)"
        });

        $("#tblLocations tbody").disableSelection();

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
            $("#sdate").val('');
            $("#type").val('');
            loadcommandfun();
        }

        function exporttopdf() {
            var q = $("#q").val();
            window.location.href = '{{ route("student-export-pdf") }}?q=' + q + '&fname=' + $("#fname").val() + '&lname=' + $("#lname").val() + '&program=' + $("#program").val() + '&status=' + $("#status").val() + '&sdate=' + $("#sdate").val() + '&type=' + $("#type").val() + '&sort_column=' + $("#sort_column").val() + '&sort_order=' + $("#sort_order").val();
        }

        function exporttoexcel() {
            var q = $("#q").val();
            window.location.href = '{{ route("student-export-excel") }}?q=' + q + '&fname=' + $("#fname").val() + '&lname=' + $("#lname").val() + '&program=' + $("#program").val() + '&status=' + $("#status").val() + '&sdate=' + $("#sdate").val() + '&type=' + $("#type").val() + '&sort_column=' + $("#sort_column").val() + '&sort_order=' + $("#sort_order").val();
        }

        function loadcommandfun(column, sort) {
            var sort_column = (column === undefined ? 's.start_date':column);
            var sort_order = (sort === undefined ? 'desc': sort);
            var q = $('#q').val();
            $.ajax({
                type: "GET",
                url: "{{ route('student-search') }}",
                //dataType: 'json',
                cache: true,
                data: {
                    q: q,
                    fname: $("#fname").val(),
                    lname: $("#lname").val(),
                    program: $("#program").val(),
                    sdate: $("#sdate").val(),
                    type: $("#type").val(),
                    sort_column: sort_column,
                    sort_order: sort_order
                },
                //data: 'q=' + q + '&fname=' + $("#fname").val() + '&lname=' + $("#lname").val() + '&program=' + $("#program").val() + '&sdate=' + $("#sdate").val() + '&type=' + $("#type").val(),
                success: function (data) {
                    /*$('#total-records').html('Total Records: '+data.total_record);
                    var str = '';
                    var data = data.result;
                    if (data.length > 0) {
                        for ($i = 0; $i < data.length; $i++) {
                            var detail_url = '{{ route("student-detail", ":id") }}';
                            detail_url = detail_url.replace(':id', data[$i].id);

                            str += '<tr id="' + data[$i].id + '">';
                            /*str += '<td data-label="">';
                            str += '<input type="checkbox" class="chk" name="ids" value="' + data[$i].id + '" >';
                            str += '</td>';
                            str += '<td data-label="">';
                            if((data[$i].total > 0)) {
                                str += '<a href="#" class="more-detail" data-id="' + data[$i].id + '" data-email="' + data[$i].email + '"> <i class="fa fa-plus" aria-hidden="true"></i></a>';
                            }
                            str += '</td>';
                            str += '<td data-label="First Name">' + data[$i].first_name + '</td>';
                            str += '<td data-label="Last Name">' + data[$i].last_name + '</td>';
                            str += '<td data-label="Email">' + data[$i].email + '</td>';

                            var program = '';
                            if((data[$i].program_name !== null)){
                                var program_arr = data[$i].program_name.split(" ");
                                program += program_arr[0] + ' ';
                                if(program_arr.length > 1){
                                    program += program_arr[1];
                                }
                                if(program_arr.length > 2){
                                    program += '...';
                                }
                            }

                            str += '<td data-label="Program" title="'+ data[$i].program_name+'">' + program + '</td>';
                            str += '<td data-label="SRP">' + (data[$i].payment_amount != null ? data[$i].payment_amount:'-') + '</td>';
                            str += '<td data-label="Paid Price">' + (data[$i].price_paid != null ? data[$i].price_paid:'-') + '</td>';
                            str += '<td data-label="Payment Type">' + data[$i].payment_type + '</td>';
                            str += '<td data-label="Start Date">' + data[$i].start_date + '</td>';
                            str += '<td data-label="End Date">' + (data[$i].end_date !== null ? data[$i].end_date:'-') + '</td>';
                            str += '<td data-label="Phone">' + ((data[$i].phone !== null) ? data[$i].phone: '-') + '</td>';
                            var street = '';
                            if((data[$i].street !== null)){
                                var street_arr = data[$i].street.split(" ");
                                street += street_arr[0] + ' ';
                                if(street_arr.length > 1){
                                    street += street_arr[1];
                                }
                                if(street_arr.length > 2){
                                    street += '...';
                                }
                            }
                            str += '<td data-label="Street" title="'+ data[$i].street +'">' + street + '</td>';
                            str += '<td data-label="City">' + ((data[$i].city !== null) ? data[$i].city: '-') + '</td>';
                            str += '<td data-label="State">' + ((data[$i].state !== null) ? data[$i].state: '-') + '</td>';
                            str += '<td data-label="Zip">' + ((data[$i].zip !== null) ? data[$i].zip: '-') + '</td>';
                            str += '<td data-label="Country">' + data[$i].country + '</td>';

                            str += '<td data-label="Student Details" class="text-center">';
                            @if(UserAccess::hasAccess(UserAccess::STUDENT_MANAGEMENT_ACCESS, 'view'))
                            str += '<a href="'+ detail_url +'" class="user-primary">';
                            str += '<i class="fa fa-id-card" aria-hidden="true"></i>';
                            str += '</a>';
                            @endif
                            str += '</td>';
                            str += '</tr>';
                        }
                    } else {
                        str += '<tr><td colspan="21" style="text-align:center;">No Record Found.</td></tr>';
                    }
                    $("#listid").html(str);*/
                    $("#listid").html(data).promise().done(function(){
                        if($('#total_count_skip').length > 0 && parseInt($('#total_count_skip').val()) > 0){
                            $('#total-records').text( 'Total Records: '+ (parseInt($('#table_id').data('total')) - parseInt($('#total_count_skip').val())) );
                        }else{
                            $('#total-records').text( 'Total Records: '+ $('#table_id').data('total') );
                        }
                    });

                    $('#table_id').dragtable({
                        dragaccept:'.accept',
                        restoreState: {!! json_encode($column_setting['column_position']) !!}
                    });

                    /*$('#table_id').DataTable({
                        searching: false,
                        paging: false,
                        info: false
                    });*/
                }
            });
        }

        window.onload = loadcommandfun();

        /*$('.checkallbox').click(function () {
            if ($(this).is(':checked') == true) {
                $('.chk').prop('checked', true);
            } else {
                $('.chk').prop('checked', false);
            }
        });*/

        var date = new Date();
        var c = date.getFullYear() + 10;
        $("#sdate").datepicker({
            dateFormat: "yy-mm-dd",
            changeMonth: true,
            changeYear: true,
            autoclose: true,
            showOn: "button",
            buttonText: '<i class="fa fa-calendar" aria-hidden="true"></i>',
            yearRange : '1940:'+c,
        });

        $(document).on('click', '.sorting', function(){
            //alert($(this).data('column') + ' ' + $(this).data('sort'));
            if($(this).data('sort') === 'asc'){
                $(this).data('sort', 'desc');
            }else{
                $(this).data('sort', 'asc');
            }
            loadcommandfun($(this).data('column'), $(this).data('sort'));
        });

        $(document).on('click', '.more-detail', function(){
            if($(this).find('i').attr('class') == 'fa fa-minus'){
                $(this).html('<i class="fa fa-plus"></i>');
                $('.'+ $(this).data('parent')).css({display: 'none'});
                /*$('.' + $(this).data('parent')).remove();*/
            }else{
                $(this).html('<i class="fa fa-minus"></i>');
                $('.'+ $(this).data('parent')).removeAttr('style');
                /*loadMore($(this).data('email'), $(this).data('id'));*/
            }
        });

        function loadMore(email, id) {
            $.ajax({
                type: "GET",
                url: "{{ route('student-load-more') }}",
                //dataType: 'json',
                cache: true,
                data: 'email=' + email + '&id=' + id,
                success: function (data) {
                    /*var str = '';
                    if (data.length > 0) {
                        for ($i = 0; $i < data.length; $i++) {
                            var detail_url = '{{ route("student-detail", ":id") }}';
                            detail_url = detail_url.replace(':id', data[$i].id);

                            str += '<tr id="' + data[$i].id + '" class="' + id + '">';
                            /*str += '<td>';
                            str += '<input type="checkbox" class="chk" name="ids" value="' + data[$i].id + '" >';
                            str += '</td>';
                            str += '<td data-label="">';
                            str += '</td>';
                            str += '<td data-label="First Name">' + data[$i].first_name + '</td>';
                            str += '<td data-label="Last Name">' + data[$i].last_name + '</td>';
                            str += '<td data-label="Email">' + data[$i].email + '</td>';

                            var program = '';
                            if((data[$i].program_name !== null)){
                                var program_arr = data[$i].program_name.split(" ");
                                program += program_arr[0] + ' ';
                                if(program_arr.length > 1){
                                    program += program_arr[1];
                                }
                                if(program_arr.length > 2){
                                    program += '...';
                                }
                            }

                            str += '<td data-label="Program" title="'+ data[$i].program_name+'">' + program + '</td>';
                            str += '<td data-label="SRP" title="'+ data[$i].payment_amount+'">' + data[$i].payment_amount + '</td>';
                            str += '<td data-label="Paid Price" title="'+ data[$i].price_paid+'">' + data[$i].price_paid + '</td>';
                            str += '<td data-label="Payment Type" title="'+ data[$i].payment_type+'">' + data[$i].payment_type + '</td>';
                            str += '<td data-label="Start Date">' + data[$i].start_date + '</td>';
                            str += '<td data-label="End Date">' + ((data[$i].end_date !== null) ? data[$i].end_date: '-') + '</td>';
                            /*str += '<td data-label="Progress">';
                            str += '<button onclick="loadrequestfun(\''+data[$i].id+'\');" class="btn btn-primary btn-sm" type="button">';
                            str += 'Request</button>';
                            str += '</td>';
                            str += '<td data-label="Phone">' + ((data[$i].phone !== null) ? data[$i].phone: '-') + '</td>';
                            var street = '';
                            if((data[$i].street !== null)){
                                var street_arr = data[$i].street.split(" ");
                                street += street_arr[0] + ' ';
                                if(street_arr.length > 1){
                                    street += street_arr[1];
                                }
                                if(street.length > 2){
                                    street += '...';
                                }

                            }
                            str += '<td data-label="Street" title="'+ data[$i].street +'">' + street + '</td>';
                            str += '<td data-label="City">' + ((data[$i].city !== null) ? data[$i].city: '-') + '</td>';
                            str += '<td data-label="State">' + ((data[$i].state !== null) ? data[$i].state: '-') + '</td>';
                            str += '<td data-label="Zip">' + ((data[$i].zip !== null) ? data[$i].zip: '-') + '</td>';
                            str += '<td data-label="Country">' + data[$i].country + '</td>';
                            str += '<td data-label="Student Details" class="text-center">';
                            str += '<a href="'+ detail_url +'" class="user-primary">';
                            str += '<i class="fa fa-id-card" aria-hidden="true"></i>';
                            str += '</a>';
                            //str += '</td>';
                            //str += '<td data-label="Student Voucher Download" class="text-center">';
                            if(data[$i].attachment !== null){
                                str += '<a href="#" class="user-primary">';
                                str += '<i class="fa fa-download download-voucher" aria-hidden="true"></i>';
                                str += '</a>';
                            }
                            str += '</td>';
                            str += '</tr>';
                        }
                    } else {
                        str += '<tr class="' + id + '"><td colspan="21" style="text-align:center;">No Record Found.</td></tr>';
                    }*/


                    $(data).insertAfter($("#"+id).closest('tr'));
                    /*$('#table_id').dragtable({
                        dragaccept:'.accept',
                        restoreState: {!! json_encode($column_setting['column_position']) !!}
                    });

                    $('#table_id').DataTable({
                        searching: false,
                        paging: false,
                        info: false
                    });*/

                    //$("#"+id).next().append(str);
                }
            });
        }

    </script>

    <script src="{{$CDN_URL}}js/jquery.form.js"></script>
<script type="text/javascript">

    $(document).on('click', '.download-voucher', function(){
        window.location.href = '/student/download-voucher/' + $(this).parents('tr').attr('id');
    });

    $(document).ready(function(){

        var visible_columns = {!! json_encode($column_setting['user_columns']) !!};
        //console.log(visible_columns);
        $.each(visible_columns, function(index, item){
            $('#'+item).hide();
            $('.'+item).hide();
        });
    });

</script>
    @stop
@stop

