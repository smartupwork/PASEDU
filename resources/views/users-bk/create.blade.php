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
                                <form name="frmname" id="myUserForm" method="post" action="{{ route('users-store')}}"  enctype="multipart/form-data">
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
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="phone" class="form-control-label">Phone <span>*</span></label>
                                                    <input maxlength="20" type="text" id="phone" name="phone" class="form-control">
                                                </div>
                                                <div class="form-group">
                                                    <label for="role" class=" form-control-label">Role <span>*</span></label>
                                                    <select name="roleid" id="roleid" class="form-control">
                                                        <option value="">Please select</option>
                                                        <option value="1">Account Manager</option>
                                                        <option value="2">Account Support</option>
                                                        <option value="3">Registration Account</option>
                                                    </select>
                                                </div>

                                                <div class="form-group" id="showpartnettype">
                                                    <div class="form-check-inline form-check">
                                                        <label for="inline-radio1" class="form-check-label ">
                                                            <input type="radio" id="augusoft_campus" name="augusoft_campus" value="1" class="form-check-input">Augusoft
                                                        </label>
                                                        <label for="inline-radio2" class="form-check-label ">
                                                            <input type="radio" id="augusoft_campus1" name="augusoft_campus" value="2" class="form-check-input">Campus CE
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label for="status" class=" form-control-label">Status <span>*</span></label>
                                                    <select name="status" id="status" class="form-control">
                                                        <option value="1">Active</option>
                                                        <option value="2">Locked</option>
                                                    </select>
                                                </div>

                                            </div>

                                        </div>
                                    </div>

                                    <div class="alert alert-danger card-body card-block m-3 print-error-msg" style="display:none">
                                        <ul></ul>
                                    </div>

                                    <div class="card-footer">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <a href="/users" class="btn btn-secondary btn-sm"> Back to Partner Users List</a>
                                                <button type="reset" class="btn btn-secondary btn-sm float-right ml-1"> Reset</button>
                                                <button type="submit" name="submit_btn" id="submit_btn" class="btn btn-primary btn-sm  float-right">Save</button>
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

{{--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>--}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
<style>
    label.has-error {
        color: #dc3545;
        font-size: 14px;
    }

    .help-block-others, .required {
        color: red !important;
    }

</style>

<script>

    $(document).ready(function() {
        /*$("#myUserForm").validate({
            rules: {
                firstname: "required",
                lastname: "required",
                roleid: "required",
                email: "required",
                phone: "required",
                status: "required",
                /!*partner: "required",
                partner_type: "required",
                augusoft_campus: {
                    required: function () {
                        if($('#partner_type').val() === '2') {
                            return true;
                        }
                        return false;
                    },
                },*!/
            },
            messages: {
                // Here you can add you custom message if required
            },
            errorElement: "div",
            errorClass: "help-block-others text-left",
            errorPlacement: function (error, element) {
                element.parent().after(error);

            },
            highlight: function (element) {
                jQuery(element).parent().addClass("has-error");
            },
            unhighlight: function (element) {
                jQuery(element).parent().removeClass("has-error");
            },
            invalidHandler: function (form, validator) {

                if (!validator.numberOfInvalids())
                    return;

                $('html, body').animate({
                    scrollTop: $(validator.errorList[0].element).offset().top - 150
                }, "fast");
            },
            submitHandler: function (form) {
                //$('#submit_btn').attr('disabled', 'disabled');
                //form.submit();
                //$('#submit_btn').attr("disabled", true);
                var formData = new FormData(form);
                $.ajax({
                    method: 'POST',
                    url: "{{ route('users-store')}}}",
                    data: formData,
                    dataType: "html",
                    success: function (response) {
                        if(response.status){
                            $('#').html(response.status);
                            alert(response.status);
                            window.location.reload();
                        }
                        //$('#statecity-div').show();
                        /!*$('#' + tab_id).html(result);
                        if (is_scroll) {
                            $("html, body").stop().animate({scrollTop: $('.support-bottom-secondbox').offset().top - 70}, 'slow');
                        }*!/
                    }
                });

            }
        });*/
    });

    $('#roleid').change(function(){
        if( $(this).val() === '3'){
            $('#showpartnettype').show();
        }else{
            $('#showpartnettype').hide();
        }
        //$('#showuserrole')[ ($("option[value='registration']").is(":checked"))? "show" : "hide" ]();
    });

    function preview(){
        $('#images').attr('src', URL.createObjectURL(event.target.files[0]));
    }

    $('#myUserForm').ajaxForm({
        beforeSubmit: function() {
            $("#submit_btn").html('Processing...');
        },
        complete: function() {
            $("#submit_btn").html('Save');
        },
        dataType: 'json',
        success: function(response) {
            $("#submit_btn").html('Save');

            if($.isEmptyObject(response.error)){
                window.location.href = '/users/edit?id='+response.lid;
            }else{
                printErrorMsg(response.error);
            }
        },
        error: function(xhr){
            if(xhr.status === 419){
                window.location.reload();
            }else{
                $("#submit_btn").html('Save');
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
    }
    function selrole(r){
        if(r == 1 || r == 2){
            $("#partner_type").html('<option value="1">Account Partner</option>');
        }
        if(r == 3){
            $("#partner_type").html('<option value="2">Registration Partner</option>');
            $("#partner").html('<option value=Not Applicable">Not Applicable</option>');
            $("#showpartnettype").show();
        }else{
            $("#showpartnettype").hide();
        }
    }*/

    function printErrorMsg (msg) {
        console.log(msg);
        $(".print-error-msg").find("ul").html('');
        $(".print-error-msg").css('display','block');
        $.each( msg, function( key, value ) {
            $(".print-error-msg").find("ul").append('<li>'+value+'</li>');
        });
    }

</script>
</body>
</html>

