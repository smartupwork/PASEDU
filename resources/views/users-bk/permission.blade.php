@include('layout.dashboard.head')
    <div class="page-wrapper">
        @include('layout.dashboard.left')
        <div class="page-container">
            @include('layout.dashboard.header')
            <div class="main-content">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        Manage Partners Permissions 
                                    </div>
                                    <form name="frmname" id="frmid" action="{{route('permissionsubmit')}}" method="post">
                                        <input type="hidden" name="ids" id="ids" value="{{$edata->id}}" />
                                        {{csrf_field()}}
                                        <div class="card-body card-block m-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="first-Name" class=" form-control-label">First Name <span>*</span></label>
                                                        <input readonly="" value="{{$edata->firstname}}" type="text" id="firstname" name="firstname"class="form-control">                                                    
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="last-name" class=" form-control-label">Last Name <span>*</span></label>
                                                        <input type="text" readonly="" id="lastname" value="{{$edata->lastname}}" name="lastname" class="form-control">
                                                    </div>
                                                 </div>
                                                 <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="email" class=" form-control-label">Email <span>*</span></label>
                                                        <input readonly="" type="text" value="{{$edata->email}}" id="email" name="email" class="form-control">                                                    
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="usertype" class=" form-control-label">User Type <span>*</span></label>
                                                        <select readonly name="role" id="role" class="form-control">
                                                                <option disabled="" value="">Please select</option>
                                                                <option disabled="" @if($edata->roleid == '1') echo {{'selected="selected"'}} @endif  value="1">Account Manager</option>
                                                                <option disabled="" @if($edata->roleid == '2') echo {{'selected="selected"'}} @endif value="2">Account Support</option>
                                                                <option disabled="" @if($edata->roleid == '3') echo {{'selected="selected"'}} @endif value="3">Registration Account</option>
                                                            </select>
                                                    </div>                                             
                                                 </div>
                                             </div>
                                             <div class="row">
                                                <div class="col-md-12">
                                                   <div class="form-group">
                                                    <p><label> Access Level <span>*</span></label></p>
                                                    <div class="form-check-inline form-check">
                                                        @if($edata->roleid == '1' || $edata->roleid == '2')
                                                            @if($edata->roleid != '2')
                                                                <label for="inline-radio1" class="form-check-label">
                                                                    <input @if($edata->access_level == '1') echo {{'checked="checked"'}} @endif type="radio" onclick="accesshide();" name="access_level" value="1" class="form-check-input">Full Access
                                                                </label>
                                                            @endif
                                                            <label for="inline-radio2" class="form-check-label">
                                                                <input @if($edata->access_level == '2') echo {{'checked="checked"'}} @endif type="radio" onclick="accesshide();" name="access_level" value="2" class="form-check-input">Account Support
                                                            </label>
                                                        @endif
                                                        @if($edata->roleid == '3')
                                                            <label for="inline-radio3" class="form-check-label">
                                                                <input @if($edata->access_level == '3') echo {{'checked="checked"'}} @endif type="radio" onclick="accesshide();" name="access_level" value="3" class="form-check-input">Registration Account Partner
                                                            </label>
                                                        @endif
                                                    </div>
                                                   </div>
                                                </div>
                                              </div>
                                            <div id="fetchaccessid"></div>
                                        </div>
                                    </form>
                                </div>
                               
                            </div>
                        </div>
                    
                    </div>
                </div>
            </div>
             </div>
    </div>
     @include('layout.dashboard.footerjs')
    <script src="{{$CDN_URL}}js/jquery.form.js"></script>
    <script type="text/javascript">
        function accesshide(){		 			
            var q = $("input[name^='access_level']:checked").val();			
            $.ajax({
                type: "GET",
                url: "/users/fetchaccess",
                data: 'q='+q,
                success: function(data){
                    $("#fetchaccessid").html(data);
                }
            });
        }
        window.onload = accesshide();
        $('#frmid').ajaxForm({		
            beforeSubmit: function() {
                $("#btnAdd").html('Processing...');
            },
            dataType: 'json',
            success: function(data) {
                $("#btnAdd").html('Save');
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
                    $("#btnAdd").html('Save');
                    alert(xhr.responseJSON.message);
                }
            }
        });
    </script>
</body>
</html>

