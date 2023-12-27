@include('layout.head')
<form name="frmname" id="frmid2" method="post" action="{{route('sendcode')}}">
    {{csrf_field()}}
    <input type="hidden" name="sids" id="sids" value="" />
</form>
   <div class="container-fluid">
      <div class="row">
        <div class="col-md-8 branding">
        </div>
        <div class="col-md-4">
            <form class="form-signin" name="frmname" id="frmid" method="post" action="{{route('submitcode')}}">
                <input type="hidden" name="ids" id="ids" value="{{$memdata->id}}" />
                {{csrf_field()}}
            <div class="text-center mb-4">
              <img class="img-fluid" src="{{$CDN_URL}}images/logo.png" alt="" width="200" />
              <h1>Email Authentication</h1>
              <p>Please click on 'Send Code' to receive a verification code sent
                   via email at: {{$email}}</p>
                   <p>If you do not receive a code, try selecting 'Resend Code' once it is enabled.</p>
            </div> 
                <div style="display: none;" id="whide" class="alert alert-warning" role="alert">
                Haven't received an email with the code? To try again, select Re-send Code.
              </div>
            <div class="input-group mb-3">
                <div class="custom-file">
                    <input maxlength="6" type="text" id="logincode" name="logincode" class="form-control"  placeholder="Enter Code">
                </div>
                <div class="input-group-append">
                    <button id="sndcode" name="sndcode" type="button" style="cursor: pointer;padding: 8px;border-radius: 5px;" onclick="sendcodefun('{{$memdata->id}}');" class="form-signin_submit">Send Code</button>
                </div>
              </div>
            <div class="checkbox mb-3">
                <label class="px-2 check-email">
                    <input value="1" type="checkbox" name="remember_me">  Do not challenge me on this device for the next 60 days.
                </label>
              </div>
                <div class="mt-4">
                    <button class="form-signin_submit" name="btnAdd" id="btnAdd" type="submit">Verify</button>
                </div>
              
          </form>
        </div>
   
      </div>
   </div>
  </body>
  <script src="{{ $CDN_URL}}js/jquery.min.js"></script>
  <script src="{{$CDN_URL}}js/jquery.form.js"></script>
  <script>
      function sendcodefun(id){
          $("#sids").val(id);
          $("#frmid2").submit();
          $("#sndcode").html("Resend Code");
          $("#whide").css("display","block");          
      }
      $('#frmid2').ajaxForm({
            beforeSubmit: function() {
                $("#sndcode").prop('disabled', true);
            },
            dataType: 'json',
            success: function(data) {
                $("#sndcode").prop('disabled', false);
                $('#logincode').val('').focus();
                if(data.status == "success"){
                    alert(data.message);	
                 }else{
                    alert(data.message);				
                }
            },
          error: function(xhr){
              if(xhr.status === 419){
                  window.location.reload();
              }else{
                  $("#sndcode").prop('disabled', false);
                  alert(xhr.responseJSON.message);
              }
          }
        });
       $('#frmid').ajaxForm({		
            beforeSubmit: function() {
            },
            dataType: 'json',
            success: function(data) {
                    if(data.status == "success"){
                        if(data.lid == '0'){
                            window.location.href = '/index/firstchangepass';
                        }else{
                            window.location.href = '/dashboard/index';
                        }
                     }else{
                        alert(data.message);				
                    }
            },
           error: function(xhr){
               if(xhr.status === 419){
                   window.location.reload();
               }else{
                   alert(xhr.responseJSON.message);
               }
           }
        });
    $(document).ready(function(){
      function fullpageWidth(){
          var newHeight = $("body").innerHeight();
          var newHeight1 = $(window).innerHeight();
          if (newHeight > newHeight1){
            $(".branding").height(newHeight);
          }
          else{
            $(".branding").height(newHeight1);
          }
      }
      fullpageWidth();
    });

    $('#logincode').keyup(function(){
        if($(this).val().length == 6){
                $('#sndcode').attr('disabled', true).css({'background': '#ced2d6'});
        }else{
            $('#sndcode').attr('disabled', false).css({'background': '#134467'});
        }
    });
    </script>
</html>
