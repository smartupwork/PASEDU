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
                                        Partner User Entry
                                    </div>
                                    <form name="frmname" id="frmid" method="post" action="{{ route('partneruserssubmit')}}" >
                                        <input type="hidden" name="id" id="id" value="{{ pas_encrypt($edata->id)}}" />
                                        {{ csrf_field()}}
                                        <div class="card-body card-block m-3">
                                            <div class="text-center">
                                                @if($edata->photo == '')
                                                    <img id="images" src="{{$CDN_URL}}dashboard/images/profile-picture.png" alt=""/>
                                                @else
                                                <img id="images" style="max-width: 200px;" src="{{env('S3_PATH')}}partner/{{$edata->photo}}" alt=""/>
                                                @endif
                                            </div>
                                            <div class="custom-file mb-3">
                                                <input type="file" accept="image/*" class="custom-file-input" onchange="preview();" id="photo" name="photo">
                                                <label class="custom-file-label" for="customFile">Choose file</label>
                                                <input type="hidden" name="old_pic" value="{{$edata->photo}}" />
                                              </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="first-Name" class=" form-control-label">First Name <span>*</span></label>
                                                        <input maxlength="40" type="text" id="firstname" value="{{ $edata->firstname}}" name="firstname"class="form-control">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="last-name" class=" form-control-label">Last Name <span>*</span></label>
                                                        <input maxlength="40" type="text" id="lastname" value="{{ $edata->lastname}}" name="lastname" class="form-control">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="role" class=" form-control-label">Role <span>*</span></label>
                                                        <select onchange="selrole(this.value);" name="role" id="role" class="form-control">
                                                            <option value="">Please select</option>
                                                            @foreach($roles as $role)
                                                                <option value="{{$role->id}}" @if($role->id == $edata->roleid) selected @endif>{{$role->role_name}}</option>
                                                            @endforeach
                                                        </select>
                                                        <input type="hidden" name="old_role" value="{{$edata->roleid}}">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="email" class=" form-control-label">Email <span>*</span></label>
                                                        <input maxlength="128" type="text" id="email" value="{{ $edata->email}}" name="email" class="form-control">
                                                    </div>
                                                 </div>
                                                 <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="phone" class="form-control-label">Phone <span>*</span></label>
                                                        <input maxlength="20" type="text" id="phone" value="{{ $edata->phone}}" name="phone" class="form-control">
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
                                                    <div class="form-group">
                                                        <label for="partner" class=" form-control-label">Partner/Institution <span>*</span></label>
                                                        <input type="hidden" id="partner" value="{{ $edata->partner}}" name="partner">

                                                        {{--<select name="partner_id" id="partner_id" class="form-control">
                                                            <option value="">Please select</option>
                                                            <option @if($edata->partner == 'Not Applicable') selected="selected" @endif value="Not Applicable">Not Applicable</option>
                                                            @if(count($partners) > 0)
                                                            @foreach($partners as $val)
                                                                <option @if($edata->partner_id == $val['id']) selected="selected" @endif value="{{ $val['id']}}">{{ $val['partner_name']}}</option>
                                                            @endforeach
                                                            @endif
                                                        </select>--}}
                                                        <select name="partner_id" id="partner_id" class="form-control">
                                                            <option value="">Please select</option>
                                                            {{--<option value="Not Applicable">Not Applicable</option>--}}
                                                            {!! $opt !!}
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="partnertype" class=" form-control-label">Partner Type <span>*</span> </label>
                                                        <select name="partner_type" id="partner_type" class="form-control">
                                                            <option value="">Please select</option>
                                                            @if(count($partner_types) > 0)
                                                                @foreach($partner_types as $partner_type)
                                                                    <option @if($edata->partner_type == $partner_type->id) selected="selected" @endif value="{{ $partner_type->id}}">{{ $partner_type->partner_type}}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                    {{--@if($edata->partner_type == '2')--}}
                                                    <div class="form-group" id="showpartnettype" style="display:{{($edata->parent_partner_name == 'Augusoft' || $edata->parent_partner_name == 'Campus CE') ? 'block':'none'}};">
                                                        <div class="form-check-inline form-check">
                                                            <label for="inline-radio1" class="form-check-label ">
                                                                <input type="radio" id="augusoft_campus" name="augusoft_campus" @if($edata->augusoft_campus == '1') checked="checked" @endif value="1" class="form-check-input augusoft">Augusoft
                                                            </label>
                                                            <label for="inline-radio2" class="form-check-label ">
                                                                <input type="radio" id="augusoft_campus1" name="augusoft_campus" @if($edata->augusoft_campus == '2') checked="checked" @endif value="2" class="form-check-input campus_ce">Campus CE
                                                            </label>
                                                        </div>
                                                    </div>
                                                    {{--@endif--}}
                                                 </div>
                                             </div>
                                        </div>
                                        <div class="card-footer">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <a href="/dashboard/partnerusers" class="btn btn-secondary btn-sm"> Back to Partner Users List</a>
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
        var roles = {!! json_encode($roles, JSON_HEX_TAG) !!};

        function selrole(r){
            if(r == 1 || r == 2){
                $("#partner_type").html('<option value="1">Account Partner</option>');
            }
            if(r == 3){
                $("#partner_type").html('<option value="2">Registration Partner</option>');
                $("#showpartnettype").show();
            }else{
                $("#showpartnettype").hide();
                //$("#partner_id").html('<option value="">Please select</option><option value="11">Not Applicable</option>{!! $opt !!} ');
            }
        }

        $('#partner_id').change(function(){
            if($(this).val() !== ''){
                $('#partner').val($(this).find("option:selected").text());
            }else{
                $('#partner').val('');
            }
        });
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

        $('#partner_id').change(function(){
            if($(this).find(':selected').data('parent') == 'Augusoft' || $(this).find(':selected').data('parent') == 'Campus CE'){
                //generateRoles(roles, 3);
                $("#partner_type").html('<option value="2">Registration Partner</option>');
                $("#role").html('<option value="3">Registration Partner</option>');

                $('#showpartnettype').show();
                if($(this).find(':selected').data('parent') == 'Augusoft'){
                    $('.campus_ce').attr('checked', false);
                    $('.augusoft').attr('checked', true);
                }else{
                    $('.augusoft').attr('checked', false);
                    $('.campus_ce').attr('checked', true);
                }

            }else{
                $('#role').empty();
                var select = $("#role");
                select.append($("<option>").attr('value', '').text('Please Select'));
                roles.map(function (arr) {
                    select.append($("<option>").attr('value', arr.id).text(arr.role_name))
                });
                $("#role").append(select);
                $("#partner_type").html('<option value="1">Account Partner</option>');
                $('#showpartnettype').hide();
            }
            //alert($(this).find(':selected').data('parent'))
        });
    </script>
</body>
</html>

