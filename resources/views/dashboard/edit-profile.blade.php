<?php
use \App\Models\UserAccess;
?>
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
                                       My Profile
                                    </div>
                                    <form name="frmname" id="frmid" action="{{ route('edit-profile-submit')}}" method="post" enctype="multipart/form-data">
                                        <input type="hidden" name="ids" id="ids" value="{{ $memdata->id}}" />
                                        {{ csrf_field()}}
                                            <div class="card-body card-block m-3">
                                                <div class="text-center">
                                                    @if($memdata->photo != '')
                                                        <img id="images" style="max-width: 200px;" src="{{env('S3_PATH')}}partner/{{ $memdata->photo}}" alt=""/>
                                                    @else
                                                        <img id="images" style="max-width: 200px;" src="{{ $CDN_URL}}dashboard/images/profile-picture.png" alt=""/>
                                                    @endif
                                                </div>
                                                <div class="custom-file mb-3">
                                                    <input type="file" class="custom-file-input" onchange="preview();" id="photo" name="photo">
                                                    <input type="hidden" name="old_pic" id="old_pic" value="{{ $memdata->photo}}" />
                                                    <label class="custom-file-label" for="customFile">Choose file</label>
                                                  </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="first-Name" class=" form-control-label">First Name <span>*</span></label>
                                                            <input readonly type="text" id="firstname" value="{{ $memdata->firstname}}" name="firstname" class="form-control">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="last-name" class=" form-control-label">Last Name <span>*</span></label>
                                                            <input readonly type="text" id="lastname" value="{{ $memdata->lastname}}" name="lastname" class="form-control">
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="role" class=" form-control-label">Role <span>*</span></label>
                                                            <select disabled="" name="role" id="role" class="form-control">
                                                                <option value="">Please select</option>
                                                                @foreach($roles as $role)
                                                                    <option value="{{$role->id}}" {{$role->id == $memdata->roleid ? 'selected':''}}>{{$role->role_name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        @if($memdata->user_type == \App\Models\User::USER_TYPE_PARTNER)
                                                        <div class="form-group">
                                                            <label for="partner" class=" form-control-label">Partner/Institution</label>
                                                            <select disabled="" name="partner" id="partner" class="form-control">
                                                                <option value="">Please select</option>
                                                                @if(count($partners) > 0)
                                                                @foreach($partners as $val)
                                                                <option @if($memdata->partner == $val['partner_name']) selected="selected" @endif  value="{{ $val['partner_name']}}">{{ $val['partner_name']}}</option>
                                                                @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                        @endif
                                                     </div>
                                                     <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="status" class=" form-control-label">Status <span>*</span></label>
                                                            <select disabled="" name="status" id="status" class="form-control">
                                                                <option value="">Please select</option>
                                                                @foreach(\App\Utility::getStatus() as $id => $status_label)
                                                                    <option value="{{$id}}" @if($memdata->status == $id) selected="selected" @endif >{{$status_label}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                         <div class="form-group">
                                                            <label for="email" class=" form-control-label">Email <span>*</span></label>
                                                            <input readonly="" value="{{$memdata->email}}" type="email" id="email" name="email" class="form-control">
                                                        </div>
                                                        @if($memdata->user_type == \App\Models\User::USER_TYPE_PARTNER)

                                                        <div class="form-group">
                                                            <label for="role" class=" form-control-label">Partner Type</label>
                                                            <select disabled="" name="partner_type" id="partner_type" class="form-control">
                                                                <option value="">Please select</option>
                                                                @if(count($partner_types) > 0)
                                                                    @foreach($partner_types as $partner_type)
                                                                        <option @if($memdata->partner_type == $partner_type->id) selected="selected" @endif value="{{ $partner_type->id}}">{{ $partner_type->partner_type}}</option>
                                                                    @endforeach
                                                                @endif
                                                            </select>
                                                        </div>
                                                        @endif
                                                        <div class="form-group">
                                                            <label for="phone" class=" form-control-label">Phone</label>
                                                            <input type="text" id="phone" value="{{ $memdata->phone}}" name="phone" class="form-control" {{($memdata->user_type == '3') ? 'readonly':''}}>
                                                        </div>
                                                     </div>
                                                 </div>
                                            </div>
                                        @if(UserAccess::hasAccess(UserAccess::MY_WE_PROFILE_ACCESS, 'add'))
                                            <div class="card-footer">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <button type="submit" id="btnAdd" name="btnAdd" class="btn btn-primary btn-sm  float-right">Save</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
     @include('layout.dashboard.footerjs')
    <script src="{{$CDN_URL}}js/jquery.form.js"></script>
    <script>
        function preview(){
            $('#images').attr('src', URL.createObjectURL(event.target.files[0]));
        }
        @if(UserAccess::hasAccess(UserAccess::MY_WE_PROFILE_ACCESS, 'add'))
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
        @endif
    </script>
</body>
</html>

