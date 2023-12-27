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
                                    <a href="https://accounts.zoho.com/oauth/v2/auth
?response_type=code&
client_id={{$_ENV['ZOHO_CLIENT_ID']}}&
scope=ZohoCRM.modules.ALL,ZohoCRM.coql.READ,ZohoCRM.bulk.ALL,ZohoCRM.modules.ALL,ZohoCRM.notifications.ALL,ZohoSearch.securesearch.READ,ZohoAnalytics.fullaccess.all,ZohoCRM.users.ALL,ZohoCRM.settings.ALL&
redirect_uri={{$_ENV['APP_URL']}}/zoho&access_type=offline" class="btn btn-secondary btn-sm">Authorize</a>
                                    </div>
                                                                         
                                </div>                               
                            </div>
                        </div>                     
                    </div>
                </div>
    </div>
     @include('layout.dashboard.footerjs')
     </div>
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

