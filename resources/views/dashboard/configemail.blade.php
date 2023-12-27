@include('layout.dashboard.head')
    <div class="page-wrapper">
        @include('layout.dashboard.left')
        <div class="page-container">
            @include('layout.dashboard.header')
            <div class="main-content">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
                        <h2 class="page-titel m-b-20">Configuration Email</h2>
                        <div class="row adv-search-row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <div class="input-group">
                                        <input class="form-control py-2 border-right-0 border" onkeyup="loadcommandfun();" type="text" placeholder="search" id="q">
                                        <span class="input-group-append">
                                            <button class="btn btn-outline-secondary border-left-0 border" type="button">
                                                <i class="fa fa-search"></i>
                                            </button>
                                        </span>
                                    </div>
                                </div> 
                            </div>
                            <div class="col-md-7 text-right pt-3">
                                <button type="button" data-toggle="modal" data-target="#ColumnSetting" class="btn btn-icon1">
                                    <i class="fa fa-cog" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive m-b-20" id="listid">
                                    
                                </div>
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-md-4">
                                <p class="text-left" id="total-records"></p>
                            </div>

                        </div>
                        @include('layout.dashboard.footer')
                    </div>
                </div>
            </div>
            <div class="modal fade" id="ColumnSetting" tabindex="-1" role="dialog" aria-labelledby="ColumnSettingLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <form name="frmid" id="frmid" action="{{ route('leads-view-header-update') }}" method="post">
                        <input type="hidden" name="module" value="configuration_email">
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
        function loadcommandfun(){		 			
                var q = $('#q').val();				
                $.ajax({
                    type: "GET",
                    url: "/dashboard/configemail/ajax",
                    //dataType : 'json',
                    cache: true,	
                    data: 'q='+q,
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
                            columnDefs: [
                                {orderable: false, targets: 3},
                                {orderable: true, className: 'reorder', targets: '_all'}
                            ]
                        });
                    }
                });
            }
            window.onload = loadcommandfun();
    </script>
</body>
</html>

