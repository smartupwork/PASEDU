@include('layout.head')
   <div class="container-fluid">
      <div class="row">

        <div class="col-md-12 pas-login text-center">

            <form method="post" action="{{ route('auth-enrollment')}}" name="frmid" id="frmid" class="form-signin">
                {{ csrf_field() }}
            <div class="text-center mb-4">
              <img class="img-fluid" src="{{$CDN_URL}}images/logo.png" alt="" width="200" />
              <h1>Welcome to World Education PAS Portal</h1>
              <p>Welcome to the PAS portal. Click below to log in with your email address and password.</p>
            </div>
            <div class="form-label-group">
                <input type="text" id="email" name="email" maxlength="118" class="form-control"  placeholder="Email address" autofocus value="kasandra_semidey@semidey.com">
            </div>

            <div class="form-label-group">
                <input type="text" name="password" id="password"  maxlength="16" placeholder="Password"  class="form-control"  value="sample">
                <button id="toggle-password" type="button" class=""
             aria-label="Show password as plain text. Warning: this will display your
              password on the screen.">
             </button>
             </div>

              <button class="form-signin_submit" type="submit" name="btnAdd" id="btnAdd">Sign in</button>
          </form>

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
                $('#btnAdd').text('Signing').prop('disabled', true);
            },
            dataType: 'json',
            success: function(data) {
                    $('#btnAdd').text('Sign In').prop('disabled', false);
                    console.log(data);
                    alert(data.message);
                    /*if(data.status == "success"){

                    }else{
                        alert(data.message);
                    }*/
            },
              error: function(xhr){
                  $('#btnAdd').text('Sign In').prop('disabled', false);
                  if(xhr.status === 419){
                      window.location.reload();
                  }else{
                      alert(xhr.responseJSON.message);
                  }
              }
        });


    </script>
  </body>
</html>
