@include('layout.dashboard.head')
    <div class="page-wrapper">
        @include('layout.dashboard.left')
        <div class="page-container">
            @include('layout.dashboard.header')
            <div class="main-content">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
                        <h2 class="page-titel m-b-20">Partner Users List</h2>
                        <div class="row adv-search-row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <div class="input-group">
                                        <input onkeyup="loadcommandfun();" class="form-control py-2 border-right-0 border" type="search" placeholder="Search" value="" id="q" name="q">
                                        <span class="input-group-append">
                                            <button class="btn btn-outline-secondary border-left-0 border" type="button">
                                                <i class="fa fa-search"></i>
                                            </button>
                                        </span>
                                        <div class="input-group-addon adv-search" data-toggle="collapse" href="#multiCollapseExample1" >
                                            <i class="fa fa-angle-double-down" aria-hidden="true"></i>
                                        </div>
                                    </div>
                                </div> 
                            </div>
                            <div class="col-md-7 text-right pt-3 pb-2">
                                <button type="button" data-toggle="modal" data-target="#ColumnSetting" class="btn btn-icon1">
                                    <i class="fa fa-cog" aria-hidden="true"></i>
                                </button>
                                <a href="/dashboard/partnerusers/add" class="btn btn-primary btn-sm">
                                    <i class="fas fa-user-plus"></i>  Add Partner Users</a>
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
                                                        <input type="text" maxlength="40" id="fname" name="fname"  class="form-control">                                                    
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="last-name" class=" form-control-label">Last Name</label>
                                                        <input type="text" maxlength="40" id="lname" name="lname"  class="form-control">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="role" class=" form-control-label">Role</label>
                                                        <select name="role" id="role" class="form-control">
                                                            <option value="">Please select</option>
                                                            @foreach($roles as $role)
                                                                <option value="{{$role->id}}">{{$role->role_name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                 </div>
                                                 <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="status" class=" form-control-label">Status</label>
                                                        <select name="status" id="status" class="form-control">
                                                            <option value="">Please select</option>
                                                            @foreach(\App\Utility::getStatus() as $id => $status_label)
                                                                <option value="{{$id}}">{{$status_label}}</option>
                                                            @endforeach
                                                        </select>                                                  
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="partner" class=" form-control-label">Partner/Institution</label>
                                                        <select name="partner" id="partner" class="form-control">
                                                            <option value="">Please select</option>
                                                            {{--<option value="Not Applicable">Not Applicable</option>--}}
                                                            @if(count($partners) > 0)
                                                                @foreach($partners as $partner)
                                                                    <option value="{{$partner['id']}}">{{$partner['partner_name']}}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="role" class=" form-control-label">Partner Type</label>
                                                        <select name="partner_type" id="partner_type" class="form-control">
                                                            <option value="">Please select</option>
                                                            @foreach($partner_types as $partner_type)
                                                                <option value="{{$partner_type->id}}">{{$partner_type->partner_type}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                 </div>
                                             </div>
                                        </form>
                                    </div>
                                    <div class="card-footer text-right">
                                        <button type="button" onclick="rstfun();" class="btn btn-secondary btn-sm float-right ml-1"> Reset</button>
                                        <button type="button" onclick="loadcommandfun();" class="btn btn-primary btn-sm">
                                            <i class="fa fa-search"></i> Search
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
                                        <button type="submit" onclick="exporttopdf();" class="btn btn-secondary btn-sm">
                                            <i class="fa fa-file-pdf"></i>  Export to PDF
                                        </button>
                                        <button type="button" onclick="delfun();" class="btn btn-danger btn-sm">
                                            <i class="fa fa-times"></i> Delete Row(s)
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>                    
                        @include('layout.dashboard.footer')
                    </div>
                </div>
            </div>
            <div class="modal fade" id="ColumnSetting" tabindex="-1" role="dialog" aria-labelledby="ColumnSettingLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <form name="frmid" id="frmid" action="{{ route('leads-view-header-update') }}" method="post">
                        <input type="hidden" name="module" value="partner_users">
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
                                  <tr style="{{ $column ==  'checkbox_sort' ? 'display:none;':'block'}};">
                                    <td class="text-center">
                                        <input type="hidden" name="position[]" value="{{$column}}">
                                        <input type="checkbox" class="{{ $column ==  'checkbox_sort' ? 'chk1':'chk'}}" name="is_visible[]" value="{{$column}}" {{ !in_array($column, $column_setting['user_columns']) ? 'checked':''}}>
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
     @include('layout.dashboard.footerjs')
     <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
     <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.24/themes/smoothness/jquery-ui.css" />
     <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.24/jquery-ui.min.js"></script>
     <script type="text/javascript" src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
     <script src="https://www.jqueryscript.net/demo/Simple-jQuery-Plugin-For-Draggable-Table-Columns-Dragtable/jquery.dragtable.js"></script>
     <script src="{{$CDN_URL}}js/jquery.form.js"></script>
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
        function rstfun(){
            $("#q").val('');
            $("#fname").val('');
            $("#lname").val('');
            $("#role").val('');
            $("#partner").val('');
            $("#partner_type").val('');
            $("#status").val('');
            loadcommandfun();
        }
        function exporttopdf(){
            var q = $("#q").val();
            window.location.href = '/dashboard/partnerusers/exportpdf?q='+q+'&fname='+$("#fname").val()+'&lname='+$("#lname").val()+'&role='+$("#role").val()+'&status='+$("#status").val()+'&partner='+$("#partner").val()+'&partner_type='+$("#partner_type").val();
        }
        function exporttoexcel(){
            var q = $("#q").val();
            window.location.href = '/dashboard/partnerusers/exportexcel?q='+q+'&fname='+$("#fname").val()+'&lname='+$("#lname").val()+'&role='+$("#role").val()+'&status='+$("#status").val()+'&partner='+$("#partner").val()+'&partner_type='+$("#partner_type").val();
        }

        var callAjax = null;

        function loadcommandfun(){
            var q = $('#q').val();
            callAjax = $.ajax({
                type: "GET",
                url: "/dashboard/partnerusers/ajax",
                //dataType : 'json',
                cache: true,
                data: 'q='+q+'&fname='+$("#fname").val()+'&lname='+$("#lname").val()+'&role='+$("#role").val()+'&status='+$("#status").val()+'&partner='+$("#partner").val()+'&partner_type='+$("#partner_type").val(),
                beforeSend : function() {
                    if(callAjax != null) {
                        callAjax.abort();
                    }
                },
                success: function(data){
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
                        ordering: true,
                        columnDefs: [
                            {orderable: false, targets: 0},
                            {orderable: false, targets: 10},
                            {orderable: true, className: 'reorder', targets: '_all'}
                        ]
                    });
                }
            });
        }




        window.onload = loadcommandfun();
        
      function delfun(){
          if($(".chklist:checked").length>0){
                    if(confirm("Are you sure you want to delete.")){
                        var valuesArray = $('input[name="ids"]:checked').map( function() {
                            return this.value;
                        }).get().join(",");
                         $.ajax({
                                url: "/dashboard/partnerusers/delete",			
                                type: "GET",
                                dataType: "json",		
                                data: 'id='+valuesArray,
                                cache: false,					
                                success: function (data){				
                                    if(data.status == "success"){
                                        var msg = '';
                                        if(data.success > 0){
                                            msg += data.success + ' records deleted successful.';
                                        }

                                        if(data.skipped > 0){
                                            msg += '\n\n' + data.skipped + ' records skipped.';
                                        }

                                        if(data.error.length > 0){
                                            msg += '\n\n Errors detail is below: \n\n';
                                            $.each(data.error, function(index, errors){
                                                    msg += errors+'\n';
                                                msg += '\n';
                                            });
                                        }
                                        alert(msg);
                                        loadcommandfun();
                                    }
                                }		
                            });
                    }
                }else{
                        alert('Please select at least one checkbox.');	
                }
                return false;
      }
    </script>
</body>
</html>

