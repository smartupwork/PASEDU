<?php
use \App\Models\UserAccess;
?>
@extends('layout.main')
@section('content')

                <div class="section__content">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        My Institution Profile
                                    </div>
                                    <div class="p-5 mb-5">
                                        <ul id="myTab2" role="tablist" class="nav nav-tabs nav-pills with-arrow lined flex-column flex-sm-row text-center">
                                            <li class="nav-item flex-sm-fill">
                                                <a id="home2-tab" data-toggle="tab" href="#home2" role="tab" aria-controls="home2" aria-selected="false" class="nav-link rounded-0 {{Session::get('active-tab') == 'logo-tab' ? 'active':''}}">Logo</a>
                                            </li>
                                            <li class="nav-item flex-sm-fill">
                                                <a id="profile2-tab" data-toggle="tab" href="#profile2" role="tab" aria-controls="profile2" aria-selected="true" class="nav-link rounded-0 {{Session::get('active-tab') == 'contact-tab' ? 'active':''}}">MyCAA Contact &amp; Title</a>
                                            </li>
                                            <li class="nav-item flex-sm-fill">
                                                <a id="contact2-tab" data-toggle="tab" href="#contact2" role="tab" aria-controls="contact2" aria-selected="false" class="nav-link rounded-0 {{Session::get('active-tab') == 'address-tab' ? 'active':''}}">Address for MyCAA</a>
                                            </li>
                                        </ul>
                                        <div id="myTab2Content" class="tab-content">
                                            <div id="home2" role="tabpanel" aria-labelledby="home-tab" class="tab-pane fade px-2 py-5 show {{Session::get('active-tab') == 'logo-tab' ? 'active':''}}">
                                                <form id="logo-form" name="logo-form" action="{{route('update-institute-logo')}}" method="post">
                                                    {{csrf_field()}}
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <div class="text-center">

                                                                @if($partner_data->logo != '')
                                                                    <img id="images" style="max-width: 200px;" src="{{env('S3_PATH')}}partner/{{ $partner_data->logo}}" class="img-fluid" alt=""/>
                                                                @else
                                                                    <img id="images" style="max-width: 200px;" src="{{ $CDN_URL}}dashboard/images/profile-picture.png" class="img-fluid" alt=""/>
                                                                @endif
                                                            </div>
                                                            <p class="text-center"><i>Attached logo to be displayed here</i></p>
                                                            <div class="row mb-3 mt-3">
                                                                <div class="col-md-6 custom-file margin-auto">
                                                                    <input type="file" class="custom-file-input" id="logo" name="logo" onchange="preview();">
                                                                    <label class="custom-file-label" for="logo">Choose file</label>
                                                                </div>
                                                            </div>
                                                            <div class="row mb-3">
                                                                <div class="col-md-6 custom-file margin-auto p-0">
                                                                    <div class="form-group">
                                                                        <input type="text" id="display_name" name="display_name" placeholder="Display Name" class="form-control" value="{{ $partner_data['partner_name'] }}" readonly>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                        @if(UserAccess::hasAccess(UserAccess::MY_INSTITUTION_PROFILE_ACCESS, 'add'))
                                                        <div class="card-footer">
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    {{--<button type="button" class="btn btn-secondary btn-sm float-right ml-1" onclick="window.location.reload()">Cancel </button>--}}
                                                                    <button type="submit" class="btn btn-primary btn-sm  float-right">Upload Logo
                                                                    </button>

                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </form>

                                            </div>
                                            <div id="profile2" role="tabpanel" aria-labelledby="profile-tab" class="tab-pane fade px-4 py-5 {{Session::get('active-tab') == 'contact-tab' ? 'active show':''}}">
                                                <form id="contact-info-form" name="contact-info-form" action="{{route('update-institute-contact')}}" method="post">
                                                    {{csrf_field()}}
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <div class="col-md-6 mx-auto">
                                                                <div class="form-group">
                                                                    <label for="contact-Name" class="form-control-label">Contact Name</label>
                                                                    <input type="text" id="contact_name" name="contact_name" class="form-control" value="{{ $partner_data['contact_name'] }}">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="title" class="form-control-label">Title</label>
                                                                    <input type="text" id="title" name="title" class="form-control" value="{{ $partner_data['title'] }}">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="phone" class="form-control-label">Phone</label>
                                                                    <input type="text" id="phone" name="phone" class="form-control" value="{{ $partner_data['phone'] }}">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="email" class="form-control-label">Email</label>
                                                                    <input type="email" id="email" name="email" class="form-control" value="{{ $partner_data['email'] }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if(UserAccess::hasAccess(UserAccess::MY_INSTITUTION_PROFILE_ACCESS, 'add'))
                                                        <div class="card-footer">
                                                            <div class="row">
                                                                <div class="col-md-12">
                                                                    {{--<button type="button" class="btn btn-secondary btn-sm float-right ml-1" onclick="window.location.reload()">Cancel </button>--}}
                                                                    <button type="submit" class="btn btn-primary btn-sm  float-right">Save
                                                                    </button>

                                                                </div>
                                                            </div>
                                                        </div>
                                                        @endif
                                                    </div>
                                                </form>
                                            </div>
                                            <div id="contact2" role="tabpanel" aria-labelledby="contact-tab" class="tab-pane fade px-4 py-5 {{Session::get('active-tab') == 'address-tab' ? 'active show':''}}">
                                                <form id="address-form" name="address-form" action="{{route('update-institute-address')}}" method="post">
                                                    {{csrf_field()}}
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="col-md-6 mx-auto">
                                                            <div class="form-group">
                                                                <label for="street" class="form-control-label">Street</label>
                                                                <input type="text" id="street" name="street" class="form-control" value="{{ $partner_data['street'] }}">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="city" class="form-control-label">City</label>
                                                                <input type="text" id="city" name="city" class="form-control" value="{{ $partner_data['city'] }}">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="state" class="form-control-label">State</label>
                                                                <select name="state" id="state" class="form-control">
                                                                    <option value="">Select State</option>
                                                                    @if($states) @foreach($states as $state)
                                                                        <option value="{{$state->id}}" {{$partner_data['state'] == $state->id ? 'selected':'' }}>{{$state->state_name}}</option>
                                                                    @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="zip_code" class="form-control-label">Zip Code</label>
                                                                <input type="text" id="zip_code" name="zip_code" class="form-control" value="{{ $partner_data['zip_code'] }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if(UserAccess::hasAccess(UserAccess::MY_INSTITUTION_PROFILE_ACCESS, 'add'))
                                                    <div class="card-footer">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                {{--<button type="button" class="btn btn-secondary btn-sm float-right ml-1" onclick="window.location.reload()">Cancel </button>--}}
                                                                <button type="submit" class="btn btn-primary btn-sm  float-right">Save
                                                                </button>

                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif
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

@section('myJsFile')

<script src="{{$CDN_URL}}js/jquery.form.js"></script>

<script>
    function preview(){
        $('#images').attr('src', URL.createObjectURL(event.target.files[0]));
    }

    @if(UserAccess::hasAccess(UserAccess::MY_INSTITUTION_PROFILE_ACCESS, 'add'))
    $('#logo-form').ajaxForm({
        beforeSubmit: function() {
            $("#logo-form").find('button[type=submit]').html('Processing...').attr('disabled', true);
        },
        dataType: 'json',
        success: function(data) {
            $("#logo-form").find('button[type=submit]').html('Upload Logo');
            if(data.status == "success"){
                alert(data.msg);
                window.location.reload();
            }else{
                alert(data.msg);
            }
        },
        complete: function(){
            $("#logo-form").find('button[type=submit]').html('Upload Logo').attr('disabled', false);
            //$('input, select').removeClass('is-invalid');
        },
        error: function(xhr){
            if(xhr.status === 419){
                window.location.reload();
            }else{
                $("#logo-form").find('button[type=submit]').html('Upload Logo').attr('disabled', false);
                alert(xhr.responseJSON.message);
            }
        }
    });

    $('#contact-info-form').ajaxForm({
        beforeSubmit: function() {
            $("#contact-info-form").find('button[type=submit]').html('Processing...').attr('disabled', true);
        },
        dataType: 'json',
        success: function(data) {
            $("#contact-info-form").find('button[type=submit]').html('Save');
            if(data.status == "success"){
                alert(data.msg);
                window.location.reload();
            }else{
                alert(data.msg);
            }
        },
        complete: function(){
            $("#contact-info-form").find('button[type=submit]').html('Save').attr('disabled', false);
            //$('input, select').removeClass('is-invalid');
        },
        error: function(xhr){
            if(xhr.status === 419){
                window.location.reload();
            }else{
                $("#contact-info-form").find('button[type=submit]').html('Save').attr('disabled', false);
                alert(xhr.responseJSON.message);
            }
        }
    });

    $('#address-form').ajaxForm({
        beforeSubmit: function() {
            $("#address-form").find('button[type=submit]').html('Processing...').attr('disabled', true);
        },
        dataType: 'json',
        success: function(data) {
            $("#address-form").find('button[type=submit]').html('Save');
            if(data.status == "success"){
                alert(data.msg);
                window.location.reload();
            }else{
                alert(data.msg);
            }
        },
        complete: function(){
            $("#address-form").find('button[type=submit]').html('Save').attr('disabled', false);
            //$('input, select').removeClass('is-invalid');
        },
        error: function(xhr){
            if(xhr.status === 419){
                window.location.reload();
            }else{
                $("#address-form").find('button[type=submit]').html('Save').attr('disabled', false);
                alert(xhr.responseJSON.message);
            }
        }
    });
    @endif

</script>
@stop
@stop