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
                                        Email Type
                                    </div>
                                    <form name="frmname" id="frmid" method="post" action="{{route('configsubmit')}}">
                                        <input type="hidden" name="id" id="id" value="{{ pas_encrypt($edata->id) }}" />
                                        {{csrf_field()}}
                                        <div class="card-body card-block m-3">
                                            <div class="row">
                                                <div class="col-md-6 mx-auto">
                                                    <div class="form-group">
                                                        <label for="from-name" class=" form-control-label">From Name</label>
                                                        <input type="text" id="from-name" name="from_name" value="{{$edata->from_name}}" class="form-control">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="from-email" class=" form-control-label">From Email</label>
                                                        <input type="text" id="from-email" name="from_email" value="{{$edata->from_email}}" class="form-control">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="first-Name" class=" form-control-label">Email Type</label>
                                                        <input type="text" id="type" name="type" value="{{ $edata->type}}"class="form-control" placeholder="Email Type" readonly>                                                    
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="last-name" class=" form-control-label">Email Subject</label>
                                                        <input type="text" id="subject" name="subject" value="{{$edata->subject}}" class="form-control">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="email" class=" form-control-label">Email Message</label>
                                                        <textarea name="message" id="message" rows="5" placeholder="Message" class="form-control">{{$edata->message}}</textarea>                                                    
                                                    </div>
                                                 </div>
                                             </div>
                                        </div>
                                        <div class="card-footer">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <a href="{{route('configuration-email')}}" class="btn btn-secondary btn-sm"> Back to Configuration Email</a>
                                                    <button type="reset" class="btn btn-secondary btn-sm float-right ml-1"> Cancel</button>
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

