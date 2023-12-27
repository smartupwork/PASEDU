@extends('layout.main')
@section('content')

<div class="section__content section__content--p30">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">Partner Inquiry Tool </div>
                    <form name="frmname" id="frmid" action="{{ route('partner-inquiry-submit') }}" method="post">
                        {{ csrf_field() }}
                        <div class="card-body card-block m-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="partnertype" class=" form-control-label">Request Type</label>
                                        <select name="request_type" id="request_type" class="form-control">
                                            <option value="">Please select</option>
                                            <option value="For Student">For Student</option>
                                            <option value="For 3rd Party Counselor/Payee">For 3rd Party Counselor/Payee</option>
                                            <option value="For Finance/Business Office">For Finance/Business Office</option>
                                            <option value="For Procurement Office">For Procurement Office</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <select name="request_reason" id="request_reason" class="form-control">
                                            <option value="">Please select</option>

                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="studentmessage" class=" form-control-label">Message</label>
                                        <textarea id="message" name="message" class="form-control"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-12">
                                    <a href="{{ route('partner-inquiry-tool-list') }}" class="btn btn-secondary btn-sm">Partner Inquiry History
                                    </a>
                                    <button type="reset" class="btn btn-secondary btn-sm float-right ml-1"> Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary btn-sm  float-right">Submit
                                    </button>
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
    <script src="{{$CDN_URL}}js/jquery.form.js"></script>

    <script>
        $(document).on('change', '#request_type', function(e){
            var t = $(this).val();
            var str = '';
            str += '<option value="">Please Select</option>';
            if(t=='For Student'){
                str +='<option value="Books/Supplies">Books/Supplies</option>';
                str +='<option value="Program (Access, Support, Completion)">Program (Access, Support, Completion)</option>';
                str +='<option value="Enrollment Assistance">Enrollment Assistance</option>';
                str +='<option value="Facilitator Response">Facilitator Response</option>';
                str +='<option value="Enrollment Assistance">Enrollment Assistance</option>';
                str +='<option value="Externship">Externship</option>';
            }else if(t=='For 3rd Party Counselor/Payee'){
                str +='<option value="Student Progress">Student Progress</option>';
                str +='<option value="Student Externship">Student Externship</option>';
                str +='<option value="Student Reporting">Student Reporting</option>';
            }else if(t=='For Finance/Business Office'){
                str +='<option value="Remittance Address">Remittance Address</option>';
                str +='<option value="Remittance ACH">Remittance ACH</option>';
                str +='<option value="Invoice Address">Invoice Address</option>';
                str +='<option value="Invoice Question">Invoice Question</option>';
            }else if(t=='For Procurement Office'){
                str +='<option value="Agreement Renewal/Extension">Agreement Renewal/Extension</option>';
                str +='<option value="New Purchase Order">New Purchase Order</option>';
                str +='<option value="Need W9">Need W9</option>';
            }
            $("#request_reason").html(str);
        });
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
@stop
@stop

