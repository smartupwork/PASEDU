<form name="contact_setting_form" id="contact_setting_form" action="{{route('prestashop-hosted-site-save')}}" method="post" enctype="multipart/form-data">
    {{csrf_field()}}
    <input type="hidden" name="id" id="contact_setting_id" value="{{ isset($edit_record) ? $edit_record->id:''}}">
    <input type="hidden" name="contact[type]" id="type" value="contact-setting">
    <div class="col-md-12">

        <div class="form-group">

            <div class="form-check">

                <div class="pl-4" id="schedule_interval_container" style="">
                    <p>
                        <label class="week"><input type="checkbox" name="contact[contact_us_opt_in]" class="form-check-input" value="1" @if(isset($edit_record) && $edit_record->is_active == 1 ) checked @elseif(!isset($edit_record)) checked @endif> Contact Us Opt In </label>
                    </p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="street" class="form-control-label">Street </label>
                    <input type="text" maxlength="100" id="street" name="contact[street]" value="{{ isset($contact_detail['street']) ? $contact_detail['street']:''}}" class="form-control">
                </div>

                <div class="form-group">
                    <label for="city" class="form-control-label">City </label>
                    <input type="text" maxlength="100" id="city" name="contact[city]" value="{{ isset($contact_detail['city']) ? $contact_detail['city']:''}}" class="form-control">
                </div>

                <div class="form-group">
                    <label for="state" class="form-control-label">State</label>
                    <select name="contact[state]" id="state" class="form-control">
                        <option value="">Select State</option>
                        @if($states) @foreach($states as $state)
                            <option value="{{$state->state_name}}" {{isset($contact_detail['state']) && $contact_detail['state'] == $state->state_name ? 'selected':'' }}>{{$state->state_name}}</option>
                        @endforeach
                        @endif
                    </select>
                </div>

                <div class="form-group">
                    <label for="zip_code" class="form-control-label">Zip Code </label>
                    <input type="text" maxlength="100" id=zip_code" name="contact[zip_code]" value="{{ isset($contact_detail['zip_code']) ? $contact_detail['zip_code']:''}}" class="form-control">
                </div>

                <div class="form-group">
                    <label for="time_zone" class="form-control-label">Time Zone</label>
                    <select name="contact[time_zone]" id="time_zone" class="form-control">
                        <option value="">Please select</option>
                        @if($timezone) @foreach($timezone as $val)
                            <option value="{{$val->timezone}}" {{isset($contact_detail['time_zone']) && $contact_detail['time_zone'] == $val->timezone ? 'selected':'' }}>{{$val->timezone}}</option>
                        @endforeach
                        @endif
                    </select>
                </div>

            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for=phone" class="form-control-label">Phone </label>
                    <input type="text" maxlength="100" id="phone" name="contact[phone]" value="{{ \App\Models\User::getPartnerDetail('pi_phone')}}" class="form-control" disabled>
                </div>

                <div class="form-group">
                    <label for="email" class="form-control-label">Email </label>
                    <input type="text" maxlength="100" id="email" name="contact[email]" value="{{\App\Models\User::getPartnerDetail('pi_email')}}" class="form-control" disabled>
                </div>

                <div class="form-group">
                    <label for="department" class="form-control-label">Department</label>
                    <input type="text" maxlength="100" id="department" name="contact[department]" value="{{ \App\Models\User::getPartnerDetail('department')}}" class="form-control" disabled>
                </div>
            </div>
        </div>





    </div>

    <div class="row">
        <div class="col-md-12">
            <button type="reset" class="btn btn-secondary btn-sm float-right ml-1"> Reset</button>
            <button type="submit" class="btn btn-primary btn-sm  float-right" id="submit-btn">Submit</button>
        </div>
    </div>

</form>
@section('myJsFile')
    <script src="{{$CDN_URL}}js/jquery.form.js"></script>
    <script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
<script>
    function preview(){
        $('#images').attr('src', URL.createObjectURL(event.target.files[0]));
    }

    $('#contact_setting_form').ajaxForm({
        beforeSerialize:function($Form, options){
            /* Before serialize */
            /*for ( instance in CKEDITOR.instances ) {
                CKEDITOR.instances[instance].updateElement();
            }
            return true;*/
        },
        beforeSubmit: function() {
            $("#submit-btn").html('Processing...');
        },
        dataType: 'json',
        success: function(data) {
            $("#submit-btn").html('Save');
            if(data.status == "success"){
                alert(data.message);
                window.location.reload();
            }else{
                alert(data.message);
            }
        },
        error: function(xhr){
            if(xhr.status === 419){
                window.location.reload();
            }else{
                $("#submit-btn").html('Save');
                alert(xhr.responseJSON.message);
            }
        }
    });
</script>
@stop