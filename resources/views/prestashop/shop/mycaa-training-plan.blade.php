@extends('layout.main')
@section('content')

    <div class="section__content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            MyCAA Training Plan Creator
                        </div>
                        <div class="card-body card-block m-3 psp-temp">
                            <form name="contact_setting_form" id="contact_setting_form" action="{{ route("download-mycaa-training") }}" method="post" enctype="multipart/form-data">
                                {{csrf_field()}}
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="city" class="form-control-label">Program</label>
                                                <select name="training[program]" id="program" class="form-control fetch-duration">
                                                    <option value="">Select Program</option>
                                                    @foreach ($program as $val)
                                                        <option value="{{ $val->id }}">{{ stripslashes($val->name) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12" style="display: none;">
                                            <div class="form-group">
                                                <input type="checkbox" name="training[without_generating]" value="1" /> Continue here without generating a new lead
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="zip_code" class="form-control-label">Course Start Date</label>
                                                <input type="text" maxlength="10" id="start_date" name="training[start_date]" value="{{ date("m/d/Y") }}" class="form-control fetch-duration">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="zip_code" class="form-control-label">Course End Date</label>
                                                <input type="text" readonly maxlength="15" id="end_date" name="training[end_date]" value="" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <h3>Student Info</h3>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="city" class="form-control-label">First Name</label>
                                                <input type="text" maxlength="50" id="first_name" name="training[first_name]" value="" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="zip_code" class="form-control-label">Last Name</label>
                                                <input type="text" maxlength="50" id="last_name" name="training[last_name]" value="" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="zip_code" class="form-control-label">Email</label>
                                                <input type="text" maxlength="118" id="email" name="training[email]" value="" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="zip_code" class="form-control-label">Phone</label>
                                                <input type="text" maxlength="15" id="phone" name="training[phone]" value="" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <input type="checkbox" name="training[turn_off_sign]" value="1" />
                                                <label for="zip_code" style="font-weight: bold;" class="form-control-label">Turn off partner signature</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="reset" class="btn btn-secondary btn-sm float-right ml-1"> Reset</button>
                                        <button type="button" class="btn btn-primary btn-sm float-right generate-plan" id="submit-btn">Generate Training Plan</button>
                                    </div>
                                </div>

                            </form>
                            @section('myCssFiles')
                                <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/css/bootstrap-datepicker.css" rel="stylesheet" media="all">
                             @stop
                    
                        @section('myJsFile')
                            <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/js/bootstrap-datepicker.js"></script>
                            <script src="{{$CDN_URL}}js/jquery.form.js"></script>
                            <script>
                                $(document).on('click', '.generate-plan', function(){
                                    if($("#program").val() == ''){
                                        alert("Please select program.");
                                        $("#program").focus();
                                        return false;
                                    }
                                    if($("#start_date").val() == ''){
                                        alert("Please select course start date.");
                                        $("#start_date").focus();
                                        return false;
                                    }
                                    if($("#end_date").val() == ''){
                                        alert("Please select course end date.");
                                        $("#end_date").focus();
                                        return false;
                                    }
                                    if($("#first_name").val() == ''){
                                        alert("Please enter first name.");
                                        $("#first_name").focus();
                                        return false;
                                    }
                                    if($("#last_name").val() == ''){
                                        alert("Please enter last name.");
                                        $("#last_name").focus();
                                        return false;
                                    }
                                    if($("#email").val() == ''){
                                        alert("Please enter email.");
                                        $("#email").focus();
                                        return false;
                                    }

                                    if($("#phone").val() == ''){
                                        alert("Please enter phone number.");
                                        $("#phone").focus();
                                        return false;
                                    }

                                    $("#contact_setting_form").submit();
                                });

                                $(document).on('change', '.fetch-duration', function(e){
                                    var id = $("#program").val();
                                    var start_date = $("#start_date").val();
                                    $.ajax({
                                        url: "{{ route('fetch-program') }}",
                                        type: "GET",
                                        dataType: "json",
                                        data: 'id=' + id +'&start_date='+start_date,
                                        cache: false,
                                        success: function (data) {
                                            $("#start_date").val(data.data.start_date);
                                            $("#end_date").val(data.data.end_date);
                                        }
                                    });
                                });
                                var date = new Date();
                                var c = date.getFullYear() + 10;
                                $("#start_date").datepicker({
                                    dateFormat: "yy-mm-dd",
                                    changeMonth: true,
                                    changeYear: true,
                                    autoclose: true,
                                    showOn: "button",
                                    buttonText: '<i class="fa fa-calendar" aria-hidden="true"></i>',
                                    yearRange : '1940:'+c,
                                });
                            </script>
                            @stop
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
