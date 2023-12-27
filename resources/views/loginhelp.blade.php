@include('layout.head')
    <div class="container-fluid">
     
        <div class="jumbotron p-0 text-white terms-bg">
          <div class="display-4 terms-bg_supportheading">
            <h1>If you have trouble logging in,<br/> follow these instructions.
            </h1>
          </div>
          <div class="col-md-12 px-0 login-support"></div>
        </div>
        <section class="terms-content">
            <div class="row margin-all">
                <div class="col-md-9">
                 <p>Resetting your password is quick and easy. All what you need is your email address and access to your email account.  </p>       
                </div>
                <div class="col-md-3">
                   <a href="/index/reset" class="reset-link">Go to password reset</a>     
                </div>
            </div>
            <div class="row margin-all">
                <div class="col-12 col-md-2 text-center"><img src="{{ $CDN_URL}}images/key-icon.png" alt=""></div>
                <div class="col-12 col-md-6">
                    <p>Please note, a verification email will be sent to your email address.</p>
                </div>
            </div>
            <div class="row margin-all">
                <div class="col-12 col-md-2 text-center"><img src="{{ $CDN_URL}}images/mail.png" alt=""></div>
                <div class="col-12 col-md-6">
                    <p> If you are unable to access your email account, please call Customer Care for assistance.</p>
                </div>
            </div>
            <div class="row margin-all">
                <h3>Password Reset Requirements must be a combination of the following:</h3>
                <ul class="col-md-4">
                    <li>8-16 Characters</li>
                    <li>Uppercase Letters</li>
                </ul>
                <ul class="col-md-4">
                    <li>Lowercase Letters</li>
                    <li>Numbers</li>
                </ul>
                <ul class="col-md-4">
                    <li>Symbols</li>
                    <li>Password cannot be your Username/User ID.</li>
                </ul>
            </div>
            <div class="row support-tip margin-all">
                <div class="col-12 col-md-2"><img class="hidden-xs" src="{{ $CDN_URL}}images/tip.png" alt=""></div>
                <div class="col-12 col-md-10">
                    <p><b>Tip:</b> Your User ID is your email address. (e.g., john@myinstitution.net). If you get a message that reads “The user ID you entered does not exist” or do not remember your email address please call Customer Care at 1-855-201-6910 for assistance.</p>
                </div>
            </div>
            <div class="row margin-all">
                <div class="col-12 col-md-8">
                  <div class="banner banner--dashboard-help">
                    <div class="row">
                      <div class="col-12 col-sm-6 box box-with-icon">
                        <div class="box__content"><img src="{{ $CDN_URL}}images/phone.png">
                          <h3 class="box__title">Call Us</h3>
                          <p>Worldeducation Customer Care Team is available 24 hours a day, seven days a week to help with technical support and general questions.</p>
                        </div>
                      </div>
                      <div class="col-12 col-sm-6">
                        <div class="heading--with-subtitle">
                          <h2 class="heading__main-title">United States</h2>
                          <p class="heading__subtitle">1-855-201-6910</p>
                        </div>
                        <div class="heading--with-subtitle">
                          <h2 class="heading__main-title">Address</h2>
                          <p class="heading__subtitle">
                            World Education <br>
                            Customer Service Team <br>
                            6777 Camp Bowie Blvd. #226 <br>
                           Ft. Worth, TX. 76116
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
        </section>
     
      </div>
  </body>
</html>
