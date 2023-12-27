<?php
use \App\Models\UserAccess;

?>
@extends('layout.main')
@section('content')

    <div class="section__content">
        <div class="container-fluid">
            <h2 class="page-titel m-b-20">Catalog Management</h2>

            <form method="post" name="searchform" id="searchform" class="form-horizontal">
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
                <div class="col-md-7 text-right">
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

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="program_name" class=" form-control-label">Program Name</label>
                                            <input type="text" id="program_name" name="program_name" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label for="course_code" class=" form-control-label">Course Code</label>
                                            <input type="text" id="course_code" name="course_code" class="form-control">
                                        </div>

                                    </div>
                                    <div class="col-md-6">
                                        {{--<div class="form-group">
                                            <label for="status" class=" form-control-label">Status</label>
                                            <select name="status" id="status" class="form-control">
                                                <option value="">Please select</option>
                                                <option value="Active">Active</option>
                                                <option value="Inactive">Inactive</option>
                                                --}}{{--<option value="Coming Soon">Coming Soon</option>--}}{{--
                                            </select>
                                        </div>--}}

                                        <div class="form-group">
                                            <label for="program_type" class=" form-control-label">Program Type</label>
                                            <select name="program_type" id="program_type" class="form-control">
                                                <option value="">Please select</option>
                                                <option value="Career Training Program">Career Training Program</option>
                                                <option value="Professional Enrichment">Professional Enrichment</option>
                                                <option value="Continuing Education">Continuing Education</option>
                                                <option value="Practice Labs">Practice Labs</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="exam_included" class=" form-control-label">Exam Included </label>
                                            <select name="certification_included" id="certification_included" class="form-control">
                                                <option value="">Please select</option>
                                                <option value="Yes">Yes</option>
                                                <option value="No">No</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                        </div>
                        <div class="card-footer text-right">
                            <button type="button" onclick="loadcommandfun();" class="btn btn-primary btn-sm">
                                <i class="fa fa-search"></i> Search
                            </button>
                            <button type="button" onclick="searchform.reset();" id="reset-button" class="btn btn-secondary btn-sm float-right ml-1">
                                Reset
                            </button>
                        </div>
                    </div>

                </div>
            </div>
            </form>

            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive m-b-20 entry-cm" id="listid">
                        
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="text-left" id="total-records">Total Records : 0</p>
                        </div>
                        <div class="col-md-8 text-right">
                            @if(UserAccess::hasAccess(UserAccess::CATALOG_MANAGEMENT_ACCESS, 'download'))
                            <button type="button" onclick="exporttoexcel();" class="btn btn-secondary btn-sm">
                                <i class="fa fa-file-excel" aria-hidden="true"></i> Export to Excel
                            </button>
                            <button type="button" onclick="exporttopdf();"  class="btn btn-secondary btn-sm">
                                <i class="fa fa-file-pdf"></i>  Export to PDF
                            </button>
                            {{--<button type="reset" class="btn btn-secondary btn-sm float-right ml-1"> Cancel
                            </button>--}}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @include('layout.dashboard.footer')
        </div>
    </div>
    <div class="modal fade" id="mediumModal" tabindex="-1" role="dialog" aria-labelledby="mediumModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <form name="frmname" id="requestfrm" method="post" action="{{ route('student-request-store')}}">
                    <div  id="resultset"></div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="ColumnSetting" tabindex="-1" role="dialog" aria-labelledby="ColumnSettingLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form name="frmid" id="frmid" action="{{ route('leads-view-header-update') }}" method="post">
                <input type="hidden" name="module" value="catalog">
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
                
                <div class="modal fade" id="mediumModal" tabindex="-1" role="dialog" aria-labelledby="mediumModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                <form name="frmname" id="requestfrm" method="post" action="{{ route('student-request-store')}}">
                    <div  id="resultset"></div>
                </form>
                </div>
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
<script type="text/javascript" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://www.jqueryscript.net/demo/Simple-jQuery-Plugin-For-Draggable-Table-Columns-Dragtable/jquery.dragtable.js"></script>
<script src="{{$CDN_URL}}js/jquery.form.js"></script>
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
        $('#reset-button').click(function(){
            loadcommandfun();
        });

        $(document).on('click', '.change-status', function(){
            var _this = this;
            if(confirm('Are you sure to '+$(this).data('action')+' "'+$(this).data('program-name')+'" this program?')){
                $(_this).attr('disabled', true);
                var program_name = $(this).data('program-name');
                var id = $(this).data('id');
                var zoho_id = $(this).data('zoho_id');
                var action = $(this).data('action');
                var list_price = $(this).data('list-price');
                $(this).data('list-price')
                $.ajax({
                    type: "POST",
                    url: "{{ route('catalog-change-status') }}",
                    dataType: 'json',
                    cache: true,
                    data: {
                        id: id,
                        zoho_id: zoho_id,
                        action: action,
                        list_price: list_price,
                        course_name: program_name
                    },
                    success: function (data) {
                        $(_this).attr('disabled', true);
                        if(data.status){
                            alert(data.message);
                            //window.location.reload();
                            loadcommandfun();
                        }else{
                            alert(data.message);
                        }
                    },
                    error: function(xhr){
                        $(_this).attr('disabled', true);
                        if(xhr.status === 419){
                            alert('Something went wrong. Please try again.');
                        }else{
                            alert(xhr.responseJSON.message);
                        }
                        window.location.reload();
                    }
                });
            }
        });

        function exporttopdf() {
            var q = $("#q").val();
            window.location.href = '{{ route("catalog-export-pdf") }}?' + $('#searchform').serialize();
        }

        function exporttoexcel() {
            var q = $("#q").val();
            window.location.href = '{{ route("catalog-export-excel") }}?' + $('#searchform').serialize();
        }

        function loadcommandfun() {
            var q = $('#q').val();
            $.ajax({
                type: "GET",
                url: "{{ route('catalog-search') }}",
                //dataType: 'json',
                cache: true,
                data: $('#searchform').serialize(),
                success: function (data) { 
                    $("#listid").html(data);    

                    $('#total-records').text( 'Total Records: '+$('#table_id').data('total'));                

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

    </script>
    
    {{--<script src="{{$CDN_URL}}js/jquery.form.js"></script>--}}

    @stop
@stop

