@include('layout.head')
   <div class="container-fluid">
      <div class="row">
        <div class="col-md-8 branding">

        </div>
        <div class="col-md-4 pas-login">

            <form method="post" action="{{ route('submit')}}" name="frmid" id="frmid" class="form-signin">
                {{ csrf_field() }}
            <div class="text-center mb-4">
              <img class="img-fluid" src="{{$CDN_URL}}images/logo.png" alt="" width="200" />
              <h1>Welcome to World Education PAS Portal</h1>
              <p>Welcome to the PAS portal. Click below to log in with your email address and password.</p>
            </div>
            <div class="form-label-group">
                <input type="text" id="email" name="email" maxlength="118" class="form-control"  placeholder="Email address" autofocus value="@if(isset($_COOKIE['email'])){{base64_decode($_COOKIE['email'])}}@endif">
            </div>

            <div class="form-label-group">
                <input type="password" name="password" id="password"  maxlength="16" placeholder="Password"  class="form-control"  value="">
                <button id="toggle-password" type="button" class=""
             aria-label="Show password as plain text. Warning: this will display your
              password on the screen.">
             </button>
             </div>
            <div class="checkbox mb-3">
              <label class="px-2">
                <input type="checkbox" value="1" name="remember" id="remember" {{ isset($_COOKIE['email']) ? 'checked' : '' }}> {{ __('Remember Me') }}
              </label>
              <label>
                  <input type="checkbox" name="agree" id="agree" value="1"> Agree With
              </label>
              <label>
               <a href="/index/termuse">Terms of Use</a>
              </label>
            </div>
              <button class="form-signin_submit" type="submit" name="btnAdd" id="btnAdd">Sign in</button>
          </form>

          <div class="login-bottom">
            <ul>
                <li>
                    <a class="button-round--primary" href="/index/loginsupport">
                        <span>I need help logging in</span>
                        <div class="caret"></div>
                    </a>
                </li>
                <li>
                    <a class="button-round--primary" href="/index/reset">
                        <span>Go to password reset</span>
                        <div class="caret"></div>
                    </a>
                </li>
            </ul>
            <span>If you still need further assistance, contact Partner Help Desk Team at
                <b>1-855-201-6910</b>
            </span>

        </div>
        </div>
      </div>
   </div>
    <div class="modal mt-10" id="mySession">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body text-center">
                    Your session on PAS portal has just expired. Do you want to sign in?
                </div>
                <div class="text-center pb-4">
                    <button type="button" class="btn btn-primary btn-sm" onclick="window.location.reload();">Yes</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="window.location.reload();">No</button>
                </div>

            </div>
        </div>
    </div>

  <script src="{{$CDN_URL}}js/jquery.min.js"></script>
  <script src="{{$CDN_URL}}js/show-password-toggle.min.js"></script>
  <script src="{{$CDN_URL}}js/jquery.form.js"></script>
<script src="{{$CDN_URL}}js/bootstrap.min.js"></script>
  <script>
      $('#frmid').ajaxForm({
            beforeSubmit: function() {
            },
            dataType: 'json',
            success: function(data) {
                    console.log(data);
                    if(data.status == "success"){
                        if($('#remember').is(":checked")){
                            document.cookie = "email="+btoa($('#email').val())+"";
                            document.cookie = "password="+btoa($('#password').val())+"";
                        }else{
                            document.cookie = "email=; expires=Thu, 01 Jan 1970 00:00:00 UTC;";
                            document.cookie = "password=; expires=Thu, 01 Jan 1970 00:00:00 UTC;";
                        }

                        if(data.lid == '1'){
                            if(data.pwd_expired){
                                alert(data.message);
                                window.location.href = '/index/firstchangepass';
                            }else if(data.first_login == '0'){
                                window.location.href = '/index/firstchangepass';
                            }else{
                                window.location.href = '/dashboard/index';
                            }
                        }else{
                            //window.location.href = '/index/emailauthentication';
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

      var request;
      var _timer = setInterval(function() {

      request = $.ajax({
          type: "POST",
          url: "{{ route('emptyxhr')}}",
          data: {'_token': $('input[name=_token]').val()},
          success: function () {

          },
          error: function (xhr) {
              if (xhr.status === 419) {
                  $('#mySession').modal({
                      backdrop: 'static',
                      keyboard: false
                  });
                  clearInterval(_timer);
                  request.abort();
              }
        }
      });

      }, 5000);
    </script>
  </body>
</html>
