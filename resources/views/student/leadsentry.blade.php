@extends('layout.main')
@section('content')

<div class="section__content section__content--p30">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        Leads Entry
                    </div>
                    <form name="frmname" id="frmid" method="post" action="{{ route('leadssubmit')}}" >
                        {{ csrf_field()}}
                        <div class="card-body card-block m-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="first-Name" class=" form-control-label">Partner Institution <span class="text-danger">*</span></label>
                                        <input type="text" maxlength="150" id="partner_institution" name="partner_institution" class="form-control" value="{{ \App\Models\User::getPartnerDetail('partner_name') }}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="last-name" class=" form-control-label">Name of Requester <span class="text-danger">*</span></label>
                                        <input type="text" maxlength="150" id="name_requester" name="name_requester" class="form-control" value="{{ Auth::user()->firstname }} {{ Auth::user()->lastname }}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="email" class=" form-control-label">Email of Requester <span class="text-danger">*</span></label>
                                        <input type="text" maxlength="120" id="email_requester" name="email_requester" class="form-control" value="{{ Auth::user()->email }}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone" class="form-control-label">First Name <span class="text-danger">*</span></label>
                                        <input type="text"  maxlength="100" id="firstname" name="firstname" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="phone" class="form-control-label">Last Name <span class="text-danger">*</span></label>
                                        <input type="text" maxlength="100" id="lastname" name="lastname" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="phone" class="form-control-label">Email <span class="text-danger">*</span></label>
                                        <input type="text" maxlength="120" id="email" name="email" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="phone" class="form-control-label">Address</label>
                                        <textarea rows="3" name="address" id="address" class="form-control"></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for="phone" class="form-control-label">Inquiry Message</label>
                                        <textarea rows="3" maxlength="300" name="message" id="message" class="form-control"></textarea>
                                    </div>

                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone" class="form-control-label">Phone</label>
                                        <input type="text" maxlength="55" id="phone" name="phone" class="form-control">
                                    </div>

                                    <div class="form-group">
                                        <label for="city" class="form-control-label">City</label>
                                        <input type="text" maxlength="55" id="city" name="city" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="state" class="form-control-label">State</label>
                                        <select name="state" id="state" class="form-control">
                                            <option value="">Select State</option>
                                            @if($states) @foreach($states as $state)
                                                <option value="{{$state->id}}">{{$state->state_name}}</option>
                                            @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="zip" class="form-control-label">Zip</label>
                                        <input type="text" maxlength="55" id="zip" name="zip" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="status" class=" form-control-label">Country</label>
                                        <select name="country" id="country" class="form-control">
                                            {{--<option value="">Select Country</option>--}}
                                            @if($countries) @foreach($countries as $country)
                                                <option value="{{$country->id}}">{{$country->country_name}}</option>
                                            @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="program_id" class="form-control-label">Interested Program</label>
                                        {{--<input type="text" maxlength="150" id="interested_program" name="interested_program" class="form-control">--}}

                                        <select name="interested_program" id="interested_program" class="form-control">
                                            <option value="">Please select</option>
                                            @foreach($programs as $program)
                                                <option value="{{$program['id']}}">{{$program['name']}}</option>
                                            @endforeach
                                        </select>

                                        {{--<input type="text" class="form-control get_program" placeholder="Program" id="get_program" autocomplete="off" name="program_name">
                                        <input type="hidden" name="interested_program" id="interested_program">--}}

                                    </div>
                                    <div class="form-group">
                                        <label for="phone" class="form-control-label">Financing Needs</label>
                                        <select name="financing_needs" id="financing_needs" class="form-control">
                                            {{--<option value="">Please select</option>--}}
                                            @foreach(\App\Models\Leads::getFinancingNeeds() as $label)
                                                <option value="{{$label}}" >{{$label}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone" class="form-control-label">Category of Interest</label>
                                        <select name="category_interest" id="category_interest" class="form-control">
                                            <option value="">Please select</option>
                                            @foreach(\App\Models\Leads::getCategoryOfInterest() as $label)
                                                <option value="{{$label}}" >{{$label}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone" class="form-control-label">Time Zone</label>
                                        <select name="time_zone" id="time_zone" class="form-control">
                                            <option value="">Please select</option>
                                            @if($timezone) @foreach($timezone as $val)
                                                <option value="{{$val->id}}">{{$val->timezone}}</option>
                                            @endforeach
                                            @endif
                                        </select>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-12">
                                    <a href="/student/leads" class="btn btn-secondary btn-sm">Back to Leads List
                                    </a>
                                    <button type="reset" class="btn btn-secondary btn-sm float-right ml-1"> Reset</button>
                                    <button name="btnAdd" id="btnAdd" type="submit" class="btn btn-primary btn-sm  float-right">Save</button>

                                </div>
                            </div>


                        </div>
                    </form>
                </div>

            </div>
        </div>

    </div>
</div>

@section('myJsFile')
    {{--<script src="{{$CDN_URL}}dashboard/js/cloneData.js"></script>--}}
    <script src="https://www.jqueryscript.net/demo/AJAX-enabled-Typeahead-Autocomplete-Plugin-For-jQuery-Bootstrap/src/bootstrap-typeahead.js"></script>
    <script src="{{$CDN_URL}}js/jquery.form.js"></script>

    <script>

        /*function preview(){
            $('#images').attr('src', URL.createObjectURL(event.target.files[0]));
        }*/
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

        {{--$('#get_program').typeahead({--}}
            {{--// data source--}}
            {{--source: [],--}}
            {{--// how many items to display--}}
            {{--items: 10,--}}
            {{--// enable scrollbar--}}
            {{--scrollBar: false,--}}
            {{--// equalize the dropdown width--}}
            {{--alignWidth: true,--}}
            {{--// typeahead dropdown template--}}
            {{--menu: '<ul class="typeahead dropdown-menu"></ul>',--}}
            {{--item: '<li><a href="#"></a></li>',--}}
            {{--// The object property that is returned when an item is selected.--}}
            {{--valueField: 'id',--}}
            {{--// The object property to match the query against and highlight in the results.--}}
            {{--displayField: 'name',--}}
            {{--// auto select--}}
            {{--autoSelect: true,--}}
            {{--// callback--}}
            {{--onSelect: function (data) {--}}
                {{--console.log(this);--}}
                {{--$('#interested_program').val(data.value);--}}
            {{--},--}}
            {{--// ajax options--}}
            {{--ajax: {--}}
                {{--url: '{{ route('get-programs') }}',--}}
                {{--timeout: 300,--}}
                {{--method: 'get',--}}
                {{--triggerLength: 3,--}}
                {{--loadingClass: null,--}}
                {{--preDispatch: null,--}}
                {{--preProcess: null--}}
            {{--}--}}
        {{--});--}}

        {{--$(document).on('keydown', '#get_program', function(e){--}}
            {{--if(e.keyCode == 8){--}}
                {{--$(this).val('');--}}
                {{--$(this).parents('.form-group').find('input[type="hidden"]').val('');--}}
            {{--}--}}
        {{--});--}}

    </script>
@stop
@stop

