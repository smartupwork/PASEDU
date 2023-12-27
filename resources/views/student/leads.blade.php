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
            <h2 class="page-titel m-b-20">Leads</h2>
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
                    @if(UserAccess::hasAccess(UserAccess::LEADS_ACCESS, 'add'))
                    <a href="{{ route('leads-add') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-user-plus"></i> Leads Entry</a>
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
                                            <label for="program" class=" form-control-label">Email</label>
                                            <input type="text" id="email" name="email" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="program" class=" form-control-label">Partner Institution</label>
                                            <input type="text" id="partner_institution" name="partner_institution" class="form-control">
                                        </div>
                                        <div class="form-label-group">
                                            <label for="example-date-input" class="col-form-label">Name of Requester</label>
                                            <input class="form-control" type="text" id="name_requester" name="name_requester">
                                        </div>

                                        <div class="form-label-group">
                                            <label for="example-date-input" class="col-form-label">Email of Requester</label>
                                            <input class="form-control" type="text" id="email_requester" name="email_requester">
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
                            @if(UserAccess::hasAccess(UserAccess::LEADS_ACCESS, 'download'))
                            <button type="button" onclick="exporttoexcel();" class="btn btn-secondary btn-sm">
                                <i class="fa fa-file-excel" aria-hidden="true"></i> Export to Excel
                            </button>
                            <button type="button" onclick="exporttopdf();" class="btn btn-secondary btn-sm">
                                <i class="fa fa-file-pdf"></i> Export to PDF
                            </button>
                            {{--<button type="button" onclick="deleteStudent();" class="btn btn-danger btn-sm">--}}
                                {{--<i class="fa fa-times"></i> Delete Row(s)--}}
                            {{--</button>--}}
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
                <input type="hidden" name="module" value="leads">
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

<!-- jQuery CDN -->
<!---<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> --->

<!-- Datatables JS CDN -->
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
            window.location.href = '{{ route("leads-export-to-pdf") }}?q=' + q + '&firstname=' + $("#fname").val() + '&lastname=' + $("#lname").val() + '&email=' + $("#email").val() + '&partner_institution=' + $("#partner_institution").val() + '&name_requester=' + $("#name_requester").val() + '&email_requester=' + $("#email_requester").val();
        }

        function exporttoexcel() {
            var q = $("#q").val();
            window.location.href = '{{ route("leads-export-to-excel") }}?q=' + q + '&firstname=' + $("#fname").val() + '&lastname=' + $("#lname").val() + '&email=' + $("#email").val() + '&partner_institution=' + $("#partner_institution").val() + '&name_requester=' + $("#name_requester").val() + '&email_requester=' + $("#email_requester").val();
        }
        $(function() {
             // DataTable
            /*$('#table_id').DataTable({
                //processing: true,
                //serverSide: true,
                paging: false,
                searching: false,
                ordering:  true,
                ajax: {
                    url: "{{route('leads-search')}}",
                    dataSrc: '',
                },
                columns: [
                    { data: 'id' },
                    { data: 'inquiry_message'},
                    { data: 'firstname'},
                    { data: 'lastname'},
                    { data: 'email'},
                    { data: 'partner_institution'},
                    { data: 'name_of_requester'},
                    { data: 'email_of_requester'},
                    { data: 'phone'},
                    { data: 'address'},
                    { data: 'country'},
                    { data: 'state'},
                    { data: 'city'},
                    { data: 'zip'},
                    { data: 'interested_program'},
                    { data: 'financing_needs'},
                    { data: 'category_of_interest'},
                    { data: 'timezone'},
                ]
            });*/
        });
        function loadcommandfun() {
            var q = $('#q').val();
            $.ajax({
                type: "GET",
                url: "{{ route('leads-search') }}",
                //dataType: 'json',
                cache: true,
                data: 'q=' + q + '&firstname=' + $("#fname").val() + '&lastname=' + $("#lname").val() + '&email=' + $("#email").val() + '&partner_institution=' + $("#partner_institution").val() + '&name_requester=' + $("#name_requester").val() + '&email_requester=' + $("#email_requester").val(),
                success: function (data) {
                    //$('#total-records').html('Total Records: '+data.length);
                    $("#listid").html(data);
                    

                    $('#table_id').dragtable({
                        dragaccept:'.accept',
                        restoreState: {!! json_encode($column_setting['column_position']) !!}
                    });

                    //$('#table_id').dragtable( {disabled:true} );

                    $('#total-records').text( 'Total Records: '+$('#table_id').data('total'));

                    $('#table_id').DataTable({
                        searching: false,
                        paging: false,
                        info: false
                    });

                    /*var str = '';
                    if (data.length > 0) {
                        for ($i = 0; $i < data.length; $i++) {
                            var url = '{{ route("leads-view", ":id") }}';
                            url = url.replace(':id', data[$i].id);

                            str += '<tr id="' + data[$i].id + '">';

                            str += '<td data-label="Leads Edit" class="text-center">';
                            str += '<a href="'+ url +'" class="user-primary">';
                            str += '<i class="fa fa-id-card" aria-hidden="true"></i>';
                            str += '</a>';
                            str += '</td>';

                            str += '<td data-label="">';
                            str += '<input type="checkbox" class="chk" name="ids" value="' + data[$i].id + '" >';
                            str += '</td>';


                            var i_message = '';
                            if((data[$i].inquiry_message !== null)){
                                var i_message_arr = data[$i].inquiry_message.split(" ");
                                i_message += i_message_arr[0] + ' ';
                                if(i_message_arr.length > 1){
                                    i_message += i_message_arr[1];
                                }
                                if(i_message_arr.length > 2){
                                    i_message += '...';
                                }
                            }

                            str += '<td data-label="Inquiry Message" title="'+data[$i].inquiry_message+'">' + ((i_message !== '') ? i_message: '-') + '</td>';

                            str += '<td data-label="First Name">' + data[$i].firstname + '</td>';
                            str += '<td data-label="Last Name">' + data[$i].lastname + '</td>';
                            str += '<td data-label="Email">' + data[$i].email + '</td>';
                            str += '<td data-label="Partner Institution">' + data[$i].partner_institution+'</td>';
                            str += '<td data-label="Name Of Requester">' + data[$i].name_of_requester + '</td>';
                            str += '<td data-label="Email Of Requester">' + data[$i].email_of_requester+ '</td>';
                            str += '<td data-label="Phone">' + ((data[$i].phone !== null) ? data[$i].phone: '-') + '</td>';
                            var address = '-';
                            if((data[$i].address !== null)){
                                address = data[$i].address.split(" ")[0]+' '+ data[$i].address.split(" ")[1]+'...';
                            }
                            str += '<td data-label="Address" title="'  + ((data[$i].address !== null) ? data[$i].address: '-') +'">' + address + '</td>';
                            str += '<td data-label="Country">' + data[$i].country + '</td>';
                            str += '<td data-label="State">' + ((data[$i].state !== null) ? data[$i].state: '-') + '</td>';
                            str += '<td data-label="City">' + ((data[$i].city !== null) ? data[$i].city: '-') + '</td>';
                            str += '<td data-label="Zip">' + ((data[$i].zip !== null) ? data[$i].zip: '-') + '</td>';

                            var program = '';
                            if((data[$i].interested_program !== null)){
                                var program_arr = data[$i].interested_program.split(" ");
                                program += program_arr[0] + ' ';
                                if(program_arr.length > 1){
                                    program += program_arr[1];
                                }
                                if(program_arr.length > 2){
                                    program += '...';
                                }
                            }

                            str += '<td data-label="Interested Program" title="'+data[$i].interested_program+'">' + ((program !== '') ? program: '-') + '</td>';
                            var f_need = '';
                            if((data[$i].financing_needs !== null)){
                                var f_need_arr = data[$i].financing_needs.split(" ");
                                f_need += f_need_arr[0] + ' ';
                                if(f_need_arr.length > 1){
                                    f_need += f_need_arr[1];
                                }
                                if(f_need_arr.length > 2){
                                    f_need += '...';
                                }
                            }

                            str += '<td data-label="Financing Needs" title="'+data[$i].financing_needs +'">' + ((f_need !== '') ? f_need: '-')+ '</td>';

                            var coi = '';
                            if((data[$i].category_of_interest !== null)){
                                var coi_arr = data[$i].category_of_interest.split(" ");
                                coi += coi_arr[0] + ' ';
                                if(coi_arr.length > 1){
                                    coi += coi_arr[1];
                                }
                                if(coi_arr.length > 2){
                                    coi += '...';
                                }
                            }

                            str += '<td data-label="Category of Interest" title="'+data[$i].category_of_interest+'">' + ((coi !== '') ? coi: '-') + '</td>';
                            str += '<td data-label="Timezone">' + ((data[$i].timezone  !== null) ? data[$i].timezone : '-')+ '</td>';

                            str += '</tr>';
                        }
                    } else {
                        str += '<tr><td colspan="23" style="text-align:center;">No Record Found.</td></tr>';
                    }
                    $("#listid").html(str);*/
                }
            });
        }

        window.onload = loadcommandfun();

        function deleteStudent() {
            if ($(".chk:checked").length > 0) {
                if (confirm("Are you sure you want to delete.")) {
                    var valuesArray = $('input[name="ids"]:checked').map(function () {
                        return this.value;
                    }).get().join(",");
                    $.ajax({
                        url: "{{ route('leads-delete') }}",
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

