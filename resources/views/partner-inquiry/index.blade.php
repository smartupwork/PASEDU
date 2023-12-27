<?php
use \App\Models\UserAccess;
?>

@extends('layout.main')
@section('myCssFiles')
{{--<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">--}}
{{--<link rel="stylesheet" type="text/css" href="https://www.jqueryscript.net/demo/Simple-jQuery-Plugin-For-Draggable-Table-Columns-Dragtable/dragtable.css" />--}}
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
@section('content')

    <div class="section__content">
        <div class="container-fluid">
            <h2 class="page-titel m-b-20">Partner Inquiry History</h2>
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
                                            <label for="first-Name" class=" form-control-label">Request Type</label>
                                            <select name="sr_request_type" id="sr_request_type" class="form-control">
                                                <option value="">Please select</option>
                                                <option value="For Student">For Student</option>
                                                <option value="For 3rd Party Counselor/Payee">For 3rd Party Counselor/Payee</option>
                                                <option value="For Finance/Business Office">For Finance/Business Office</option>
                                                <option value="For Procurement Office">For Procurement Office</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="last-name" class=" form-control-label">Request Reason</label>
                                            <select name="sr_request_reason" id="sr_request_reason" class="form-control">
                                                <option value="">Please select</option>
                                                
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
                            <button type="button" onclick="exporttoexcel();" class="btn btn-secondary btn-sm">
                                <i class="fa fa-file-excel" aria-hidden="true"></i> Export to Excel
                            </button>
                            <button type="button" onclick="exporttopdf();" class="btn btn-secondary btn-sm">
                                <i class="fa fa-file-pdf"></i> Export to PDF
                            </button>
                            <button type="button" onclick="deletePartner();" class="btn btn-danger btn-sm">
                                <i class="fa fa-times"></i> Delete Row(s)
                            </button>
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
                <input type="hidden" name="module" value="partner_inquiry">
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
                          <tr>
                            <td class="text-center">
                                <input type="hidden" name="position[]" value="{{$column}}">
                                <input type="checkbox" class="chk" name="is_visible[]" value="{{$column}}" {{ !in_array($column, $column_setting['user_columns']) ? 'checked':''}}>
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
@section('myJsFile')
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.24/themes/smoothness/jquery-ui.css" />
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.24/jquery-ui.min.js"></script>



<!-- Datatables JS CDN -->
<script type="text/javascript" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://www.jqueryscript.net/demo/Simple-jQuery-Plugin-For-Draggable-Table-Columns-Dragtable/jquery.dragtable.js"></script>

<script src="{{$CDN_URL}}js/jquery.form.js"></script>
    <script>
        $(document).on('change', '#sr_request_type', function(e){
            var t = $(this).val();
            var str = '';
            str += '<option value="">Please Select</option>';
            if(t=='For Student'){
                str +='<option value="Books/Supplies">Books/Supplies</option>';
                str +='<option value="Program (Access, Support, Completion)">Program (Access, Support, Completion)</option>';
                str +='<option value="Enrollment Assistance">Enrollment Assistance</option>';
                str +='<option value="Facilitator Response">Facilitator Response</option>';
                str +='<option value="Enrollment Assistance">Enrollment Assistance</option>';
                str +='<option value="Externship">Externship</option>';
            }else if(t=='For 3rd Party Counselor/Payee'){
                str +='<option value="Student Progress">Student Progress</option>';
                str +='<option value="Student Externship">Student Externship</option>';
                str +='<option value="Student Reporting">Student Reporting</option>';
            }else if(t=='For Finance/Business Office'){
                str +='<option value="Remittance Address">Remittance Address</option>';
                str +='<option value="Remittance ACH">Remittance ACH</option>';
                str +='<option value="Invoice Address">Invoice Address</option>';
                str +='<option value="Invoice Question">Invoice Question</option>';
            }else if(t=='For Procurement Office'){
                str +='<option value="Agreement Renewal/Extension">Agreement Renewal/Extension</option>';
                str +='<option value="New Purchase Order">New Purchase Order</option>';
                str +='<option value="Need W9">Need W9</option>';
            }
            $("#sr_request_reason").html(str);
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
            $("#email").val('');
            $("#partner_institution").val('');
            $("#name_requester").val('');
            $("#email_requester").val('');
            loadcommandfun();
        }

        function exporttopdf() {
            var q = $("#q").val();
            window.location.href = '{{ route("partner-inquiry-export-to-pdf") }}?q=' + q+'&request_type='+$('#sr_request_type').val()+'&request_reason='+$('#sr_request_reason').val();
        }

        function exporttoexcel() {
            var q = $("#q").val();
            window.location.href = '{{ route("partner-inquiry-export-to-excel") }}?q=' + q+'&request_type='+$('#sr_request_type').val()+'&request_reason='+$('#sr_request_reason').val();
        }
        function loadcommandfun() {
            var q = $('#q').val();
            $.ajax({
                type: "GET",
                url: "{{ route('partner-inquiry-search') }}",
                //dataType: 'json',
                cache: true,
                data: 'q=' + q+'&request_type='+$('#sr_request_type').val()+'&request_reason='+$('#sr_request_reason').val(),
                success: function (data) {
                    $("#listid").html(data);
                    

                    $('#table_id').dragtable({
                        dragaccept:'.accept',
                        restoreState: {!! json_encode($column_setting['column_position']) !!}
                    });

                    $('#table_id').DataTable({
                        searching: false,
                        paging: false,
                        info: false
                    });
                }
            });
        }

        window.onload = loadcommandfun();

        

        function deletePartner() {
            if ($(".chklist:checked").length > 0) {
                if (confirm("Are you sure you want to delete.")) {
                    var valuesArray = $('input[name="ids"]:checked').map(function () {
                        return this.value;
                    }).get().join(",");
                    $.ajax({
                        url: "{{ route('partner-inquiry-delete') }}",
                        type: "GET",
                        dataType: "json",
                        data: 'id=' + valuesArray,
                        cache: false,
                        success: function (data) {
                            if (data.status == "success") {
                                loadcommandfun();
                            }
                        }
                    });
                }
            } else {
                alert('Please select at least one checkbox.');
            }
            return false;
        }

    </script>
    @stop
@stop

