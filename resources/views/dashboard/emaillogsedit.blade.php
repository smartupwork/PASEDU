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
                                    <div class="card-header">Email Type </div>
                                    <form name="frmname" id="frmid" method="post" action="{{ route('emaillogssubmit')}}">
                                        {{csrf_field()}}
                                        <div class="card-body card-block m-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="last-name" class=" form-control-label">To</label>
                                                        <input type="text" id="to_email" value="{{$edata->to_email}}" name="to_email" class="form-control" placeholder="Email" readonly>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="email" class=" form-control-label">Date</label>
                                                        <input type="text" id="date" name="date" class="form-control" value="{{$edata->added_date}}" placeholder="Date" readonly>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="email" class=" form-control-label">Email Subject</label>
                                                        <input type="text" id="subject" name="subject" value="{{$edata->subject}}" class="form-control" placeholder="Subject">
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="email" class=" form-control-label">Email Message</label>
                                                        <textarea class="form-control" rows="5" name="message" id="message">{{$edata->message}}</textarea>
                                                    </div>
                                                </div>
                                             </div>
                                        </div>
                                        <div class="card-footer">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <a href="{{route('system-email-logs')}}" class="btn btn-secondary btn-sm">Back to System Email Logs</a>
                                                    <button type="reset" class="btn btn-secondary btn-sm float-right ml-1">Cancel</button>
                                                    <button type="submit" class="btn btn-primary btn-sm  float-right">Resend</button>                                                
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
    <script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
    <script>
        CKEDITOR.replace( 'message' );
        //CKEDITOR.instances['message'].updateElement();

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
</body>
</html>

