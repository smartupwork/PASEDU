@include('layout.head')
   <div class="container-fluid">
      <div class="row">
        <div class="col-md-8 branding">

        </div>
        <div class="col-md-4">
            <form name="frmname" id="frmid" method="post" action="{{ route('resetpass')}}" class="form-signin">
                {{ csrf_field()}}
            <div class="text-center mb-4">
              <img class="img-fluid" src="{{ $CDN_URL}}images/logo.png" alt="" width="200" />
              <h1>Forgot your Password?</h1>
              <p>Enter your email and we will send you a reset link.</p>
            </div>     
            <div class="form-label-group">
                <input type="text" name="email" maxlength="118" id="inputEmail" class="form-control"  placeholder="Email address">
            </div>
    
                <button class="form-signin_submit" type="submit" name="btnAdd" id="btnAdd">Send Request</button>
                <a class="form-signin_submit secondary float-right" href="/">Back</a>
          </form>
        </div>
   
      </div>
   </div>
  </body>
  <script src="{{ $CDN_URL}}js/jquery.min.js"></script>
  <script src="{{$CDN_URL}}js/jquery.form.js"></script>
  <script>
      $('#frmid').ajaxForm({		
            beforeSubmit: function() {
            },
            dataType: 'json',
            success: function(data) {
                    if(data.status == "success"){
                            alert(data.msg);
                            window.location.href = '/index/changepass';
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
