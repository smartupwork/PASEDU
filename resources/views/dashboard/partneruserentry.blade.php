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
                                    <form name="frmname" id="frmid" method="post" action="{{ route('partneruserssubmit')}}"  enctype="multipart/form-data">
                                        <input type="hidden" name="ids" id="ids" value="" />
                                        {{ csrf_field()}}
                                        <div class="card-body card-block m-3">
                                            <div class="text-center">
                                                <img id="images" src="{{$CDN_URL}}dashboard/images/profile-picture.png" alt=""/>
                                            </div>
                                            <div class="custom-file mb-3">
                                                <input type="file" accept="image/*" class="custom-file-input" onchange="preview();" id="photo" name="photo">
                                                <label class="custom-file-label" for="customFile">Choose file</label>
                                              </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="first-Name" class=" form-control-label">First Name <span>*</span></label>
                                                        <input maxlength="40" type="text" id="firstname" name="firstname"class="form-control">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="last-name" class=" form-control-label">Last Name <span>*</span></label>
                                                        <input maxlength="40" type="text" id="lastname" value="" name="lastname" class="form-control">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="email" class=" form-control-label">Email <span>*</span></label>
                                                        <input maxlength="128" type="text" id="email" name="email" class="form-control">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="phone" class="form-control-label">Phone <span>*</span></label>
                                                        <input maxlength="20" type="text" id="phone" name="phone" class="form-control">
                                                    </div>
                                                 </div>
                                                 <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="status" class=" form-control-label">Status <span>*</span></label>
                                                        <select name="status" id="status" class="form-control">
                                                            @foreach(\App\Utility::getStatus() as $id => $status_label)
                                                                <option value="{{$id}}">{{$status_label}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="partner" class=" form-control-label">Partner/Institution <span>*</span></label>
                                                        <select name="partner_id" id="partner_id" class="form-control">
                                                            {{--<option value="Not Applicable">Not Applicable</option>--}}
                                                            {!! $opt !!}
                                                        </select>
                                                    </div>
                                                     <div class="form-group">
                                                         <label for="role" class=" form-control-label">Role <span>*</span></label>
                                                         <select onchange="selrole(this.value);" name="role" id="role" class="form-control">
                                                             <option value="">Please select</option>
                                                             @foreach($roles as $role)
                                                                 <option value="{{$role->id}}">{{$role->role_name}}</option>
                                                             @endforeach
                                                         </select>
                                                     </div>
                                                    <div class="form-group">
                                                        <label for="partnertype" class=" form-control-label">Partner Type <span>*</span> </label>
                                                        <select name="partner_type" id="partner_type" class="form-control">
                                                            <option value="">Please select</option>
                                                            @foreach($partner_types as $partner_type)
                                                                <option value="{{$partner_type->id}}">{{$partner_type->partner_type}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group" id="showpartnettype">
                                                        <div class="form-check-inline form-check">
                                                            <label for="inline-radio1" class="form-check-label ">
                                                                <input type="radio" id="augusoft_campus" name="augusoft_campus" value="1" class="form-check-input augusoft">Augusoft
                                                            </label>
                                                            <label for="inline-radio2" class="form-check-label ">
                                                                <input type="radio" id="augusoft_campus1" name="augusoft_campus" value="2" class="form-check-input campus_ce">Campus CE
                                                            </label>
                                                        </div>
                                                    </div>
                                                 </div>
                                             </div>
                                        </div>
                                        <div class="card-footer">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <a href="/dashboard/partnerusers" class="btn btn-secondary btn-sm"> Back to Partner Users List</a>
                                                    <button type="reset" class="btn btn-secondary btn-sm float-right ml-1" onclick="rstfun()";> Reset</button>
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
        //var partners = {!! json_encode($partners, JSON_HEX_TAG) !!}; // Currently not using
        //console.log(partners);
        var roles = {!! json_encode($roles, JSON_HEX_TAG) !!};

        /*var roles = [
            {'id': 1, "name": 'Account Manager'},
            {'id': 2, "name": 'Account Support'},
            {'id': 3, "name": 'Registration Account'}
        ];*/

        function preview(){
            $('#images').attr('src', URL.createObjectURL(event.target.files[0]));
        }

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

        /*function selins(i){
            if(i == 'Not Applicable'){
                $("#partner_type").html('<option value="2">Registration Partner</option>');
                $("#showpartnettype").show();
            }else{
                $("#partner_type").html('<option value="1">Account Partner</option>');
                $("#showpartnettype").hide();
            }
        }*/

        function selrole(r){
            if(r == 1 || r == 2){
                $("#partner_type").html('<option value="1">Account Partner</option>');
            }
            if(r == 3){
                $("#partner_type").html('<option value="2">Registration Partner</option>');
                //$("#partner_id").html('<option value=11">Not Applicable</option>');
                //$("#partner_id").html('<option value="">Please select</option><option value="11">Not Applicable</option>{!! $opt !!} ');
                $("#showpartnettype").show();
            }else{
                $("#showpartnettype").hide();
                //$("#partner_id").html('<option value="">Please select</option><option value="11">Not Applicable</option>{!! $opt !!} ');
            }
        }
        function rstfun(){
            $("#partner_type").html('<option value="">Please select</option><option value="2">Registration Partner</option>');
            $("#showpartnettype").hide();

            $("#partner_id").html('{!! $opt !!} ');
        }

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

