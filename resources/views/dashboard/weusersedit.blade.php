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
                                        WE User Entry
                                    </div>
                                    <form name="frmname" id="frmid" method="post" action="{{ route('weuserssubmit')}}">
                                        <input type="hidden" name="id" id="id" value="{{ pas_encrypt($edata->id)}}" />
                                        {{ csrf_field()}}
                                        <div class="card-body card-block m-3">
                                            <div class="text-center">
                                                @if($edata->photo == '')
                                                    <img id="images" src="{{$CDN_URL}}dashboard/images/profile-picture.png" alt=""/>
                                                @else
                                                    <img style="max-width: 200px;" id="images" src="{{env('S3_PATH')}}partner/{{$edata->photo}}" alt=""/>
                                                @endif
                                            </div>
                                            <div class="custom-file mb-3">
                                                <input type="file" class="custom-file-input" onchange="preview();" id="photo" name="photo">
                                                <label class="custom-file-label" for="customFile">Choose file</label>
                                                <input type="hidden" name="old_pic" value="{{$edata->photo}}" />
                                              </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="first-Name" class=" form-control-label">First Name <span>*</span></label>
                                                        <input type="text" id="firstname" value="{{ $edata->firstname}}" name="firstname" class="form-control">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="last-name" class=" form-control-label">Last Name <span>*</span></label>
                                                        <input type="text" id="lastname" value="{{ $edata->lastname}}" name="lastname" class="form-control">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="email" class=" form-control-label">Email <span>*</span></label>
                                                        <input type="text" id="email" value="{{$edata->email}}" name="email" class="form-control">
                                                    </div>
                                                 </div>
                                                 <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="phone" class="form-control-label">Phone <span>*</span></label>
                                                        <input type="text" id="phone" value="{{ $edata->phone}}" name="phone" class="form-control">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="userrole" class=" form-control-label">Role <span>*</span></label>
                                                        <select name="role" id="role" class="form-control">
                                                            <option value="">Please select</option>
                                                            @foreach($roles as $role)
                                                                <option value="{{$role->id}}" @if($role->id == $edata->roleid) selected="selected" @endif>{{$role->role_name}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="status" class=" form-control-label">Status <span>*</span></label>
                                                        <select name="status" id="status" class="form-control">
                                                            <option value="">Please select</option>
                                                            @foreach(\App\Utility::getStatus() as $id => $status_label)
                                                                <option value="{{$id}}" @if($edata->status == $id) selected="selected" @endif >{{$status_label}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                 </div>
                                             </div>
                                        </div>
                                        <div class="card-footer">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <a href="/dashboard/weusers" class="btn btn-secondary btn-sm">Back to WE Users List</a>
                                                    <button type="reset" class="btn btn-secondary btn-sm float-right ml-1"> Reset</button>
                                                    <button type="submit" name="btnAdd" id="btnAdd" class="btn btn-primary btn-sm  float-right">Save</button>
                                                </div>
                                            </div>


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
    <script>
        function preview(){
            $('#images').attr('src', URL.createObjectURL(event.target.files[0]));
        };
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

