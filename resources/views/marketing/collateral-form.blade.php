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
                            Request Collateral
                        </div>
                        @if(UserAccess::hasAccess(UserAccess::REQUEST_COLLATERAL_ACCESS, 'add'))
                        <form name="collateral-from" id="collateral-from" action="{{route('marketing-collateral-store')}}" method="post" enctype="multipart/form-data">
                            {{@csrf_field()}}
                            @endif
                        <div class="card-body card-block m-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contact_name" class=" form-control-label">Contact Name</label>
                                        <input type="text" id="contact_name" name="contact_name" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="contact_email" class=" form-control-label">Contact Mail</label>
                                        <input type="text" id="contact_email" name="contact_email" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="partner_name" class=" form-control-label">Institution/Partner Name</label>
                                        <input type="text" id="partner_name" name="partner_name" class="form-control" value="{{ \App\Models\User::getPartnerDetail('partner_name') }}" readonly>
                                    </div>
                                    <div class="form-group">
                                        Is requested material for an event?
                                        <div class="form-check-inline form-check">
                                            <label for="inline-radio1" class="form-check-label ">
                                                <input type="radio" id="is_requested_material_yes" name="is_requested_material" value="1" class="form-check-input">Yes
                                            </label>
                                            <label for="inline-radio2" class="form-check-label ">
                                                <input type="radio" id="is_requested_material_no" name="is_requested_material" value="0" class="form-check-input">No
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group" id="event_date_div" style="display: none;">
                                        <div class="form-label-group">
                                            <label for="event_date1" class=" form-control-label">Date of event</label>
                                            <input readonly type="text" id="event_date" name="event_date" class="form-control">
                                            <label class="input-group-btn" for="event_date">
                                                <span class="form-date" style="top: 40px;">
                                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="target_audience" class=" form-control-label">What is the target audience for these materials?</label>
                                        <input type="text" id="target_audience" name="target_audience" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="intended_outcome" class=" form-control-label">What is the intended outcome of the materials? </label>
                                        <input type="text" id="intended_outcome" name="intended_outcome" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="branding" class=" form-control-label">Branding</label>
                                        <select name="branding" id="branding" class="form-control">
                                            <option value="">Please select</option>
                                            @foreach(\App\Models\MarketingCollateral::getBrandingType() as $id => $label)
                                                <option value="{{$id}}">{{$label}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <div class="form-label-group">
                                            <label for="due_date1" class=" form-control-label">Project due date </label>
                                            <input readonly type="text" id="due_date" name="due_date" class="form-control">
                                            <label class="input-group-btn" for="due_date">
                                                <span class="form-date" style="top: 40px;">
                                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="form-label-group">
                                            <label for="desired_completion_date1" class=" form-control-label">Desired Completion Date</label>
                                            <input readonly type="text" id="desired_completion_date" name="desired_completion_date" class="form-control">
                                            <label class="input-group-btn" for="desired_completion_date">
                                                <span class="form-date" style="top: 40px;">
                                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                                </span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="form-label-group">
                                            <label for="meeting_proposed_date1" class=" form-control-label">Meeting Proposed Date</label>
                                            <input readonly type="text" id="meeting_proposed_date" name="meeting_proposed_date" class="form-control">
                                            <label class="input-group-btn" for="meeting_proposed_date">
                                                <span class="form-date" style="top: 40px;">
                                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="project_type" class=" form-control-label">Project type </label>
                                        <select name="project_type" id="project_type" class="form-control">
                                            <option value="">Please select</option>
                                            @foreach(\App\Models\MarketingCollateral::getProjectType() as $id => $label)
                                                <option value="{{$id}}">{{$label}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="program_id" class=" form-control-label">Course(s) being promoted </label>
                                        <select name="program_id" id="program_id" class="form-control">
                                            <option value="">Please select</option>
                                            @foreach($programs as $program)
                                                <option value="{{$program['id']}}">{{$program['name']}}</option>
                                            @endforeach
                                        </select>
                                        {{--<input type="text" class="form-control get_program" placeholder="" id="get_program" autocomplete="off" name="program_name">

                                        <input type="hidden" maxlength="150" id="program_id" value="" name="program_id" class="form-control">--}}
                                    </div>
                                    <div class="form-group">
                                        <label for="description" class=" form-control-label">Description of project </label>
                                        <textarea name="description" id="description" rows="7" placeholder="Content..." class="form-control"></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for="additional_notes" class=" form-control-label">Any additional notes </label>
                                        <textarea name="additional_notes" id="additional_notes" rows="7" placeholder="Content..." class="form-control"></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for="purpose" class=" form-control-label">Purpose</label>
                                        <textarea name="purpose" id="purpose" rows="7" placeholder="Content..." class="form-control"></textarea>
                                    </div>

                                </div>
                            </div>
                            <div class="checkbox mb-3">
                                <label>
                                    <input type="checkbox" name="remember_me" id="remember_me" value="1"> I understand that custom marketing materials could take 7-10 business days to complete, or more for larger projects
                                </label>
                                <label>
                                    <input type="checkbox" name="agree_with" id="agree_with" value="1">  I understand that this request is for digital materials only. Printing, posting, and postage will be my responsibility
                                </label>
                            </div>
                        </div>
                            @if(UserAccess::hasAccess(UserAccess::REQUEST_COLLATERAL_ACCESS, 'add'))
                        <div class="card-footer">
                        <div class="row">
                            <div class="col-md-12">
                                <button type="reset" class="btn btn-secondary btn-sm float-right ml-1"> Reset
                                </button>
                                <button type="submit" class="btn btn-primary btn-sm  float-right">Save
                                </button>

                            </div>
                        </div>

                            @endif
                            @if(UserAccess::hasAccess(UserAccess::REQUEST_COLLATERAL_ACCESS, 'add'))
                        </form>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </div>


@section('myCssFiles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/css/bootstrap-datepicker.css" rel="stylesheet" media="all">
    {{--<style>
        .inline-ckeditor p{ width: 600px;margin-left:10px;}
        .inline-ckeditor{
            border: 1px solid #e2e5e7;
        }
    </style>--}}
@stop
@section('myJsFile')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/js/bootstrap-datepicker.js"></script>
    <script src="https://www.jqueryscript.net/demo/AJAX-enabled-Typeahead-Autocomplete-Plugin-For-jQuery-Bootstrap/src/bootstrap-typeahead.js"></script>
    <script src="{{$CDN_URL}}js/jquery.form.js"></script>

    {{--<script src="{{ asset('ckeditor/ckeditor.js') }}"></script>--}}
    <script>

        /*$('.ckeditor').each(function(e){
            CKEDITOR.inline( this.id, { customConfig: '/ckeditor/config-basic.js' });
        });*/

        //CKEDITOR.replace( 'message' );

        $('#collateral-from').ajaxForm({
            beforeSubmit: function() {
                $("#btnAdd").html('Processing...')//.attr('disabled', true);
            },
            dataType: 'json',
            success: function(data) {
                $('input, select').removeClass('is-invalid');
                $("#btnAdd").html('Save');//.attr('disabled', false);
                if(data.status == "success"){
                    alert(data.msg);
                    window.location.reload();
                }else if(data.errors){
                    var all_error = '';
                    $.each(data.errors, function(input_name, errors){
                            $('#'+ input_name).addClass('is-invalid');
                            all_error += errors+ '\r\n';
                    });
                    if(all_error !== ''){
                        alert(all_error);
                    }
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

        /*$('#get_program').typeahead({
            // data source
            source: [],
            // how many items to display
            items: 10,
            // enable scrollbar
            scrollBar: false,
            // equalize the dropdown width
            alignWidth: true,
            // typeahead dropdown template
            menu: '<ul class="typeahead dropdown-menu"></ul>',
            item: '<li><a href="#"></a></li>',
            // The object property that is returned when an item is selected.
            valueField: 'id',
            // The object property to match the query against and highlight in the results.
            displayField: 'name',
            // auto select
            autoSelect: true,
            // callback
            onSelect: function (data) {
                console.log(this);
                $('#program_id').val(data.value);
            },
            // ajax options
            ajax: {
                url: '{{ route('get-programs') }}',
                timeout: 300,
                method: 'get',
                triggerLength: 3,
                loadingClass: null,
                preDispatch: null,
                preProcess: null
            }
        });

        $(document).on('keydown', '#get_program', function(e){
            if(e.keyCode == 8){
                $(this).val('');
                $(this).parents('.form-group').find('input[type="hidden"]').val('');
            }
        });*/

        $(function() {

            var date = new Date();
            var c = date.getFullYear() + 10;
            $("#event_date").datepicker({
                dateFormat: "yy-mm-dd",
                changeMonth: true,
                changeYear: true,
                autoclose: true,
                showOn: "button",
                buttonText: '<i class="fa fa-calendar" aria-hidden="true"></i>',
                yearRange : '1940:'+c,
            });
            $("#due_date").datepicker({
                dateFormat: "yy-mm-dd",
                changeMonth: true,
                changeYear: true,
                autoclose: true,
                showOn: "button",
                buttonText: '<i class="fa fa-calendar" aria-hidden="true"></i>',
                yearRange : '1940:'+c,
            });

            $("#desired_completion_date").datepicker({
                dateFormat: "yy-mm-dd",
                changeMonth: true,
                changeYear: true,
                autoclose: true,
                showOn: "button",
                buttonText: '<i class="fa fa-calendar" aria-hidden="true"></i>',
                yearRange : '1940:'+c,
            });

            $("#meeting_proposed_date").datepicker({
                dateFormat: "yy-mm-dd",
                changeMonth: true,
                changeYear: true,
                autoclose: true,
                showOn: "button",
                buttonText: '<i class="fa fa-calendar" aria-hidden="true"></i>',
                yearRange : '1940:'+c,
            });

            $('#event_date_div').hide();
            $("input[name='is_requested_material']").click(function() {
                if ($("input[name='is_requested_material']:checked").val() == 1) {
                    $("#event_date_div").show();
                } else {
                    $("#event_date_div").hide();
                }
            });
        });

    </script>
    @stop
@stop

