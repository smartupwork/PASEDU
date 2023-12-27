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
                                        Manage My User Permissions
                                    </div>
                                    <form name="frmname" id="frmid" action="{{route('mpermissionsubmit')}}" method="post">
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
                                                 </div>
                                             </div>
                                             <div class="row">
                                                <div class="col-md-12">
                                                   <div class="form-group">
                                                    <p><label> Access Level <span>*</span></label></p>
                                                    <div class="form-check-inline form-check">
                                                        <label for="inline-radio1" class="form-check-label">
                                                            <input type="radio" onclick="fullaccess();" id="inline-radio1" name="access_level" value="{{\App\Models\User::ACCESS_LEVEL_FULL}}" class="form-check-input">Full Access
                                                        </label>

                                                        @if($edata->roleid == \App\Models\User::ROLE_ACCOUNT_SUPPORT)

                                                            <label for="inline-radio2" class="form-check-label">
                                                                <input @if($edata->access_level == \App\Models\User::ACCESS_LEVEL_ACCOUNT_SUPPORT)  {{'checked="checked"'}} @endif type="radio" onclick="accesshide();" id="inline-radio2" name="access_level" value="{{\App\Models\User::ACCESS_LEVEL_ACCOUNT_SUPPORT}}" class="form-check-input">Account Support
                                                            </label>
                                                        @endif

                                                        @if($edata->roleid == \App\Models\User::ROLE_ACCOUNT_MANAGER)
                                                            <label for="inline-radio3" class="form-check-label">
                                                                <input @if($edata->access_level == \App\Models\User::ACCESS_LEVEL_ACCOUNT_MANAGER)  {{'checked="checked"'}} @endif type="radio" onclick="accesshide();" id="inline-radio3" name="access_level" value="{{\App\Models\User::ACCESS_LEVEL_ACCOUNT_MANAGER}}" class="form-check-input">Account Manager
                                                            </label>

                                                        @endif

                                                        @if($edata->roleid == \App\Models\User::ROLE_REGISTRATION_ACCOUNT)
                                                            <label for="inline-radio3" class="form-check-label">
                                                                <input @if($edata->access_level == \App\Models\User::ACCESS_LEVEL_REGISTRATION_ACCOUNT_PARTNER)  {{'checked="checked"'}} @endif type="radio" onclick="accesshide();" id="inline-radio3" name="access_level" value="{{\App\Models\User::ACCESS_LEVEL_ACCOUNT_MANAGER}}" class="form-check-input">Account Manager
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

        function fullaccess(){
            $('input[type="checkbox"]').attr('checked', true);
            $('input[type="radio"][value|="1"]').attr('checked', true);
        }

        function accesshide(){
            var al = $("input[name^='access_level']:checked").val();
            $.ajax({
                type: "GET",
                url: "{{route('my-users-fetch-access')}}",
                data: 'uid={{pas_encrypt($edata->id)}}&ur={{$edata->roleid}}&ut={{$edata->user_type}}&al='+al,
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

