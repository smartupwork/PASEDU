@extends('layout.main')
@section('content')

    <div class="section__content">
        <div class="container-fluid">
            <h2 class="page-titel m-b-20">Student Enrollment</h2>
            <div class="row m-b-20">
                <div class="col-md-12 text-right">
                    <a href="{{ route("student-template-file") }}" class="btn btn-secondary btn-sm btn-action" title="Import Template File">
                        <i class="fa fa-download" aria-hidden="true"></i>
                    </a>
                    <button class="btn btn-secondary btn-sm btn-action" data-toggle="modal" data-target="#infomodal">
                        <i class="fa fa-info" aria-hidden="true"></i>
                    </button>
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#mediumModal"> Student Mass Enrollment </button>
                </div>
            </div>

            @include('student._form')

            @include('layout.dashboard.footer')
        </div>
    </div>

    <div class="modal fade" id="mediumModal" tabindex="-1" role="dialog" aria-labelledby="mediumModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <form name="frmname" id="frmid2" action="{{route('student-import-file')}}" enctype="multipart/form-data">
                {{csrf_field()}}
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="mediumModalLabel"> Student Mass Enrollment</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="custom-file mb-3">
                            <input type="file"  class="custom-file-input" id="customFile" name="filename">
                            <label class="custom-file-label" for="customFile">Choose file</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="btnAdd" id="btnAdd" class="btn btn-primary btn-sm">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="infomodal" tabindex="-1" role="dialog" aria-labelledby="infomodalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infomodalLabel">Student Management</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="modal-infor">
                        <li>Click on the Import Template File link, to download the excel template file.</li>
                        <li>Use the Import Template file as the template to import your Student Enrollment data.</li>
                        <li>Make sure all mandatory columns on the template have values in them. Mandatory columns are in bold.</li>
                        <li>Rename and Save your new data to a copy of this template on your computer
                    </ul>
                    <ol class="info m-3">
                        <li>Click on Browse File and choose the file you have saved in Step 1 above.</li>
                        <li>Now click on the Import button.</li>
                        <li>You will see the imported records displayed on the Student List screen.</li>
                        <li>Review data and make sure everything is correct. Hence delete bad records, if there are any.</li>
                        <li>Import log is recorded on the Student Import Audit screen. </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>


@section('myCssFiles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/css/bootstrap-datepicker.css" rel="stylesheet" media="all">
    <style>
        .typeahead li a{
            font-size: 10px; white-space: nowrap; height: 10px;
        }
        /*.modal-backdrop{display: none;}

        label{
            width: 30px;
            height: 22px;
            background: none;
            color: #6c757d;
            border: 1px solid #6c757d;
        }*/
    </style>
@stop

@section('myJsFile')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/js/bootstrap-datepicker.js"></script>
    <script src="https://www.jqueryscript.net/demo/AJAX-enabled-Typeahead-Autocomplete-Plugin-For-jQuery-Bootstrap/src/bootstrap-typeahead.js"></script>
    <script src="{{ asset('js/cloneData.js') }}"></script>
    <script src="{{$CDN_URL}}js/jquery.form.js"></script>

    <script>
        $(document).ready(function(){
            var date = new Date();
            var c = date.getFullYear() + 10;
            $("#start_date_0").datepicker({
                dateFormat: "yy-mm-dd",
                changeMonth: true,
                changeYear: true,
                autoclose: true,
                showOn: "button",
                buttonText: '<i class="fa fa-calendar" aria-hidden="true"></i>',
                yearRange : '1940:'+c,
            });
            $("#complete_date_0").datepicker({
                dateFormat: "yy-mm-dd",
                changeMonth: true,
                changeYear: true,
                autoclose: true,
                showOn: "button",
                buttonText: '<i class="fa fa-calendar" aria-hidden="true"></i>',
                yearRange : '1940:'+c,
            });
            $("#end_date_0").datepicker({
                dateFormat: "yy-mm-dd",
                changeMonth: true,
                changeYear: true,
                autoclose: true,
                showOn: "button",
                buttonText: '<i class="fa fa-calendar" aria-hidden="true"></i>',
                yearRange : '1940:'+c,
            });

            $('button.add-student').cloneData({
                mainContainerId: 'student-container',
                cloneContainer: 'student-div',
                removeButtonClass: 'remove-student',
                removeConfirm: true,
                removeConfirmMessage: 'Are you sure to remove?',
                minLimit: 1,
                maxLimit: 25,
                afterRender: function () {
                    var index = this.index;
                    $("#start_date_"+index).datepicker({
                        dateFormat: "yy-mm-dd",
                        changeMonth: true,
                        changeYear: true,
                        autoclose: true,
                        showOn: "button",
                        buttonText: '<i class="fa fa-calendar" aria-hidden="true"></i>',
                        yearRange : '1940:'+c,
                    });
                    $("#complete_date_"+index).datepicker({
                        dateFormat: "yy-mm-dd",
                        changeMonth: true,
                        changeYear: true,
                        autoclose: true,
                        showOn: "button",
                        buttonText: '<i class="fa fa-calendar" aria-hidden="true"></i>',
                        yearRange : '1940:'+c,
                    });
                    $("#end_date_"+index).datepicker({
                        dateFormat: "yy-mm-dd",
                        changeMonth: true,
                        changeYear: true,
                        autoclose: true,
                        showOn: "button",
                        buttonText: '<i class="fa fa-calendar" aria-hidden="true"></i>',
                        yearRange : '1940:'+c,
                    });

                    $('#get_program_' + index).typeahead({
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
                            $('#program_id_' + index).val(data.value);
                        },
                        // ajax options
                        ajax: {
                            url: '{{ route('get-programs') }}',
                            timeout: 300,
                            method: 'get',
                            triggerLength: 1,
                            loadingClass: null,
                            preDispatch: null,
                            preProcess: null
                        }
                    });

                }
            });
        });
        $('#frmid2').ajaxForm({
            beforeSubmit: function() {
                $("#submit-btn").html('Processing...')//.attr('disabled', true);
            },
            dataType: 'json',
            success: function(data) {
                $("#btnAdd").html('Save')//.attr('disabled', false);
                if(data.status == "success"){
                    var msg = data.success + ' records imported successful.';
                    msg += '\n\n' + data.skipped.length + ' records skipped.';

                    if(data.error.length > 0){
                        msg += '\n\n Errors detail is below: \n\n';
                        msg += data.error.join('\n\n')
                    }

                    if(data.zoho_errors.length > 0){
                        msg = '\n\n Zoho Errors is below: \n';
                        msg += data.zoho_errors.join('\n\n')
                    }
                    alert(msg);
                    //console.log(data.error);
                    window.location.reload();
                }else if(data.status == "fail"){
                    alert(data.msg);
                    window.location.reload();
                }
            },
            complete: function(){
                $("#submit-btn").html('Save').attr('disabled', false);
                //$('input, select').removeClass('is-invalid');
            },
            error: function(xhr){
                if(xhr.status === 419){
                    window.location.reload();
                }else{
                    $("#submit-btn").html('Save').attr('disabled', false);
                    alert(xhr.responseJSON.message);
                }
            }
        });
        $('#frmid').ajaxForm({
            beforeSubmit: function() {
                $("#submit-btn").html('Processing...').attr('disabled', true);
            },
            dataType: 'json',
            success: function(data) {
                $("#submit-btn").html('Save').attr('disabled', false);
                $('input, select').removeClass('is-invalid');
                if(data.status == "success"){
                    alert(data.msg);
                    window.location.reload();
                }else{
                    if(data.zoho_errors){
                        alert('ZOHO Error Response: '+data.zoho_errors);
                    }else if(data.errors){
                        var all_error = '';
                        $.each(data.errors, function(index, errors){
                            $.each(errors, function(input_name, item){
                                //console.log(input_name, item);
                                all_error += item +'\r\n';
                                $('#'+ input_name + '_' + index).addClass('is-invalid');
                            });
                            all_error += '\r\n';
                        });
                        /*if(data.duplicate_email_found){
                            if(!confirm('System will use email address to detect that.This is a duplicate record. Do you want to proceed?')){
                                $('#duplicate_allow').val(0);
                            }else{
                                $('#duplicate_allow').val(1);
                                $('#frmid').submit();
                            }
                        }*/
                        if(all_error !== ''){
                            alert(all_error);
                        }
                    }

                }
            },
            complete: function(){
                $("#submit-btn").html('Save').attr('disabled', false);
                //$('input, select').removeClass('is-invalid');
            },
            error: function(xhr){
                if(xhr.status === 419){
                    window.location.reload();
                }else{
                    $("#submit-btn").html('Save').attr('disabled', false);
                    alert(xhr.responseJSON.message);
                }
            }
        });

        $('#get_program_0').typeahead({
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
                $('#program_id_0').val(data.value);
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

        $(document).on('keydown', '.get_program', function(e){
            if(e.keyCode == 8){
                $(this).val('');
            }
            $(this).parents('.form-group').find('input[type="hidden"]').val('');
        })

    </script>
@stop
@stop

