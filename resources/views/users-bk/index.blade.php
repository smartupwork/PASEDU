@include('layout.dashboard.head')
    <div class="page-wrapper">
        @include('layout.dashboard.left')
        <div class="page-container">
            @include('layout.dashboard.header')
            <div class="main-content">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
                        <h2 class="page-titel m-b-20">My Users List</h2>
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
                            <div class="col-md-7 text-right">
                                <a href="/users/create" class="btn btn-primary btn-sm">
                                    <i class="fas fa-user-plus"></i>  Add My User</a>
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
                                                        <label for="email" class=" form-control-label">Email</label>
                                                        <input type="text" maxlength="40" id="email" name="email"  class="form-control">
                                                    </div>
                                                 </div>
                                                 <div class="col-md-6">
                                                     <div class="form-group">
                                                         <label for="phone" class=" form-control-label">Phone</label>
                                                         <input type="text" maxlength="40" id="phone" name="phone"  class="form-control">
                                                     </div>
                                                     <div class="form-group">
                                                         <label for="role" class=" form-control-label">Role</label>
                                                         <select name="role" id="role" class="form-control">
                                                             <option value="">Please select</option>
                                                             <option value="1">Account Manager</option>
                                                             <option value="2">Account Support</option>
                                                         </select>
                                                     </div>
                                                     <div class="form-group">
                                                         <label for="status" class=" form-control-label">Status</label>
                                                         <select name="status" id="status" class="form-control">
                                                             <option value="">Please select</option>
                                                             <option value="1">Active</option>
                                                             <option value="2">Locked</option>
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
                                <div class="table-responsive m-b-20">
                                    <table class="table table-earning data-table">
                                        <thead>
                                            <tr>
                                                <th>
                                                    <input type="checkbox" class="checkallbox">
                                                </th>
                                                <th>First Name</th>
                                                <th>Last Name</th>
                                                <th>Role</th>
                                                <th>Status</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="listid">

                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-right">
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
                        @include('layout.dashboard.footer')
                    </div>
                </div>
            </div>
     @include('layout.dashboard.footerjs')
    <script>
        function rstfun(){
            $("#q").val('');
            $("#fname").val('');
            $("#lname").val('');
            $("#role").val('');
            $("#status").val('');
            loadcommandfun();
        }
        function exporttopdf(){
            var q = $("#q").val();
            window.location.href = '/users/exportpdf?q='+q+'&fname='+$("#fname").val()+'&lname='+$("#lname").val()+'&role='+$("#role").val()+'&status='+$("#status").val();
        }
        function exporttoexcel(){
            var q = $("#q").val();
            window.location.href = '/users/exportexcel?q='+q+'&fname='+$("#fname").val()+'&lname='+$("#lname").val()+'&role='+$("#role").val()+'&status='+$("#status").val();
        }
        function loadcommandfun(){
                var q = $('#q').val();
                $.ajax({
                    type: "GET",
                    url: "/users/ajax",
                    dataType : 'json',
                    cache: true,
                    data: 'q='+q+'&fname='+$("#fname").val()+'&lname='+$("#lname").val()+'&role='+$("#role").val()+'&status='+$("#status").val(),
                    success: function(data){
                            var str = '';
                            if(data.length > 0){
                                for($i=0; $i < data.length; $i++){
                                    str += '<tr>';
                                    str += '<td data-label="">';
                                    str += '<input type="checkbox" class="chk" name="ids" value="'+data[$i].id+'" >';
                                    str += '</td>';
                                    str += '<td data-label="First Name">'+data[$i].firstname+'</td>';
                                    str += '<td data-label="Last Name">'+data[$i].lastname+'</td>';
                                    str += '<td data-label="Role">'+data[$i].roleid+'</td>';
                                    str += '<td data-label="Status">'+data[$i].status+'</td>';
                                    str += '<td data-label="Email">'+data[$i].email+'</td>';
                                    //str += '<td data-label="Partner / Institution">'+data[$i].partner_name+'</td>';
                                    str += '<td data-label="Phone">'+data[$i].phone+'</td>';
                                    //str += '<td data-label="Partner Type">'+data[$i].partner_type+'</td>';
                                    str += '<td>';
                                    str += '<div class="manage">';
                                    str += '<a title="Edit" href="/users/edit?id='+data[$i].id+'">';
                                    str += '<i class="fa fa-id-card" aria-hidden="true"></i>';
                                    str += '</a>';
                                    str += '<a href="/users/permission?id='+data[$i].id+'" class="lock">';
                                    str += '<i class="fa fa-lock" aria-hidden="true"></i>';
                                    str += '</a>';
                                    str += '</div>';
                                    str += '</td>';
                                    str += '</tr>';
                                }
                            }else{
                                str += '<tr><td colspan="10" style="text-align:center;">No Record Found.</td></tr>';
                            }
                            $("#listid").html(str);
                    }
                });
            }
window.onload = loadcommandfun();

        $('.checkallbox').click(function(){
            if($(this).is(':checked') == true){
                    $('.chk').prop('checked',true);
            }else{
                    $('.chk').prop('checked',false);
            }
        });

      function delfun(){
          if($(".chk:checked").length>0){
                    if(confirm("Are you sure you want to delete.")){
                        var valuesArray = $('input[name="ids"]:checked').map( function() {
                            return this.value;
                        }).get().join(",");
                         $.ajax({
                                url: "/users/delete",
                                type: "GET",
                                dataType: "json",
                                data: 'id='+valuesArray,
                                cache: false,
                                success: function (data){
                                         if(data.status === "success"){
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

