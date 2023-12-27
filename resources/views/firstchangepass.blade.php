@include('layout.head')
<style>
    button#toggle-pass {
        position: absolute;
        top: 15px;
        right: 12px;
        z-index: 9;
        width: 28px;
        height: 30px;
        background: 0;
        border: 0;
    }

    button#toggle-cpass {
        position: absolute;
        top: 15px;
        right: 12px;
        z-index: 9;
        width: 28px;
        height: 30px;
        background: 0;
        border: 0;
    }
</style>
   <div class="container-fluid">
      <div class="row">
        <div class="col-md-8 branding"> </div>
        <div class="col-md-4 pas-login">
            <form name="frmname" method="post" id="frmid" action="{{route('firstchangepasswordsubmit')}}" class="form-signin">
                <input type="hidden" name="ids" id="ids" value="{{$memdata->id}}" />
              {{ csrf_field()}}
            <div class="text-center mb-4">
              <img class="img-fluid" src="{{$CDN_URL}}images/logo.png" alt="" width="200" />
            </div>
            <div class="form-label-group">
                <input type="password"  maxlength="16" id="pass" name="pass"   placeholder="New Password" class="form-control input-password" autofocus tabindex="1">
                <button id="toggle-pass" type="button" class="" aria-label="Show password as plain text. Warning: this will display your  password on the screen.">
             </button>
            </div>

            <div class="form-label-group">
                <input type="password" maxlength="16" id="cpass" name="cpass"  placeholder="Confirm Password" class="form-control input-password" tabindex="2">
                <button id="toggle-cpass" type="button" class="" aria-label="Show password as plain text. Warning: this will display your password on the screen.">
             </button>
            </div>
            <button name="btnAdd" id="btnAdd" type="submit" class="form-signin_submit">Change Password</button>
          </form>
          <div>
              <h2>Your password must:</h2>
              <ul>
                  <li>Include uppercase and lowercase letters</li>
                  <li>Include at least one number and one symbol (such as "!" or "$")</li>
                  <li>Not include your first, last, or email user name</li>
                  <li>Your password must be between 10 and 16 characters</li>
              </ul>
          </div>
        </div>
      </div>
   </div>
  </body>
  <script src="{{ $CDN_URL}}js/jquery.min.js"></script>
  <script src="{{$CDN_URL}}js/jquery.form.js"></script>
  <script>
      document.getElementById("pass").classList.add("input-password"),
          document.getElementById("toggle-pass").classList.remove("d-none");
      const passInput=document.getElementById("pass"),
          togglePassButton=document.getElementById("toggle-pass");
      togglePassButton.addEventListener("click",togglePass);
      function togglePass()
      {"password"===passInput.type?
          (passInput.type="text",togglePassButton.setAttribute("aria-label","Hide password.")):
          (passInput.type="password",togglePassButton.setAttribute
          ("aria-label","Show password as plain text. Warning: this will display your password on the screen."))};

      document.getElementById("cpass").classList.add("input-password"),
          document.getElementById("toggle-cpass").classList.remove("d-none");
      const cpassInput=document.getElementById("cpass"),
          togglecPassButton=document.getElementById("toggle-cpass");
      togglecPassButton.addEventListener("click",togglecPass);
      function togglecPass()
      {"password"===cpassInput.type?
          (cpassInput.type="text",togglecPassButton.setAttribute("aria-label","Hide password.")):
          (cpassInput.type="password",togglecPassButton.setAttribute
          ("aria-label","Show password as plain text. Warning: this will display your password on the screen."))};

      $('#frmid').ajaxForm({
            beforeSubmit: function() {
            },
            dataType: 'json',
            success: function(data) {
                    if(data.status == "success"){
                            window.location.href = '/dashboard/index';
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
    </script>
</html>
