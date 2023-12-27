<header class="header-mobile d-block d-lg-none">
    <div class="header-mobile__bar">
        <div class="container-fluid">
            <div class="header-mobile-inner">
                <a class="logo" href="{{route('dashboard')}}">
                    <img src="{{$CDN_URL}}dashboard/images/icon/logo.png" alt="" />
                </a>
                <button class="hamburger hamburger--slider" type="button">
                            <span class="hamburger-box">
                                <span class="fas fa-bars"></span>
                            </span>
                </button>
            </div>
        </div>
    </div>
    <nav class="navbar-mobile">
        <div class="container-fluid">
            <ul class="navbar-mobile__list list-unstyled">
                <li class="has-sub">
                    <a class="js-arrow" href="#">
                        <i class="fa fa-tachometer-alt"></i>Dashboard
                        <span class="navarrow">
                                    <i class="fas fa-angle-down"></i>
                                </span>
                    </a>
                    <ul class="navbar-mobile-sub__list list-unstyled js-sub-list">
                        <li>
                            <a href="{{route('dashboard')}}">
                                My Dashboard</a>
                        </li>
                        <li>
                            <a href="{{route('myinstitution')}}">
                                My Institution Request</a>
                        </li>
                        {{--<li>--}}
                        {{--<a href="WE-Admin-Dashboard.html">--}}
                        {{--WE Admin Dashboard</a>--}}
                        {{--</li>--}}
                        {{--<li>
                            <a href="Map-Partners.html">
                              Map Partners</a>
                        </li>--}}
                        <li>
                            <a href="{{route('map-my-student')}}">
                                Map My Students</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="{{route('catalog-listing')}}"><i class="fas fa-copy"></i>Catalog Management</a>
                </li>
                <li class="has-sub">
                    <a class="js-arrow" href="#">
                        <i class="fa fa-graduation-cap" aria-hidden="true"></i>Student Management
                        <span class="navarrow">
                                    <i class="fas fa-angle-down"></i>
                                </span>
                    </a>
                    <ul class="navbar-mobile-sub__list list-unstyled js-sub-list">
                        <li>
                            <a href="{{route('student-list')}}">
                                Student Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="{{route('import-audit')}}">
                                Student Import Audit
                            </a>
                        </li>
                        <li>
                            <a href="{{route('leads')}}">
                                Leads
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="has-sub">
                    <a class="js-arrow" href="#">
                        <i class="fa far fa-chart-bar" aria-hidden="true"></i>Marketing
                        <span class="navarrow">
                                    <i class="fas fa-angle-down"></i>
                                </span>
                    </a>
                    <ul class="navbar-mobile-sub__list list-unstyled js-sub-list">
                        <li>
                            <a href="{{route('announcement', ['announce_type' => 'news'])}}">
                                News
                            </a>
                        </li>
                        <li>
                            <a href="{{route('announcement', ['announce_type' => 'announcements'])}}">
                                Announcements
                            </a>
                        </li>
                        <li>
                            <a href="{{route('announcement', ['announce_type' => 'updates'])}}">
                                Updates
                            </a>
                        </li>
                        <li>
                            <a href="{{route('top-selling-programs')}}">
                                My Top Selling Programs
                            </a>
                        </li>
                        <li>
                            <a href="{{route('request-collateral')}}">
                                Request Collateral
                            </a>
                        </li>
                        <li>
                            <a href="{{route('marketing-collateral')}}">
                                Marketing Collateral
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="has-sub">
                    <a class="js-arrow" href="#">
                        <i class="fas fa-address-book"></i>Partner Profile
                        <span class="navarrow">
                                    <i class="fas fa-angle-down"></i>
                                </span>
                    </a>
                    <ul class="navbar-mobile-sub__list list-unstyled js-sub-list">
                        <li>
                            <a href="{{route('institute-profile')}}">
                                My Institution Profile</a>
                        </li>
                        <li>
                            <a href="{{route('my-profile')}}">
                                My Profile</a>
                        </li>
                    </ul>
                </li>
                @if(Auth::user()->user_type == '3')
                    <li>
                        <a href="{{ route('my-profile') }}">
                            <i class="fas fa-file"></i>My Profile</a>
                    </li>
                @endif
                <li class="has-sub">
                    <a class="js-arrow" href="#">
                        <i class="far fa-user"></i>PAS Admin
                        <span class="navarrow">
                                    <i class="fas fa-angle-down"></i>
                                </span>
                    </a>
                    <ul class="navbar-mobile-sub__list list-unstyled js-sub-list">
                        <li>
                            <a href="{{route('partner-users')}}">
                                Partner Users List</a>
                        </li>
                        <li>
                            <a href="{{route('we-users')}}">
                                WE Users List </a>
                        </li>
                        <li>
                            <a href="{{route('configuration-email')}}">
                                Configuration Email</a>
                        </li>
                        <li>
                            <a href="{{route('system-email-logs')}}">
                                System Email Logs</a>
                        </li>
                        <li style="display: none;">
                            <a href="Manage-Permissions-Partners.html">
                                Manage Permissions-Partners</a>
                        </li>
                    </ul>
                </li>
                <li class="has-sub">
                    <a class="js-arrow" href="#">
                        <i class="fa fa-users"></i>Partner Admin
                        <span class="navarrow">
                                    <i class="fas fa-angle-down"></i>
                                </span>
                    </a>
                    <ul class="navbar-mobile-sub__list list-unstyled js-sub-list">
                        <li>
                            <a href="{{route('my-users')}}">
                                My Users List</a>
                        </li>
                        <li style="display: none;">
                            <a href="Permission-Assignment.html">
                                Permission Assignment</a>
                        </li>
                    </ul>
                </li>

                <li class="has-sub">
                    <a class="js-arrow" href="#">
                        <i class="fas fa-list-alt" aria-hidden="true"></i>PAS CMS
                        <span class="navarrow">
                                    <i class="fas fa-angle-down"></i>
                                </span>
                    </a>
                    <ul class="navbar-mobile-sub__list list-unstyled js-sub-list">
                        <li>
                            <a href="{{route('marketing-form')}}">
                                Partner Announcement
                            </a>
                        </li>
                        <li>
                            <a href="{{route('we-templates')}}">
                                We Templates
                            </a>
                        </li>
                        {{--<li>--}}
                        {{--<a href="{{route('marketing-collateral')}}">--}}
                        {{--Request Collateral--}}
                        {{--</a>--}}
                        {{--</li>--}}
                    </ul>
                </li>

            </ul>

        </div>
    </nav>
</header>
<aside class="menu-sidebar d-none d-lg-block">
    <div class="logo">
        <a href="{{ route('dashboard') }}">
            <img src="{{$CDN_URL}}dashboard/images/icon/logo.png" alt="" />
        </a>
    </div>
    <div class="menu-sidebar__content js-scrollbar1">
        @if(!Auth::guest())
            <div class="account2">
                <div class="image img-cir img-120">
                    @if(Auth::user()->photo != '')
                        <img src="{{env('S3_PATH')}}partner/{{Auth::user()->photo}}" alt=""/>
                    @else
                        <img src="{{$CDN_URL}}dashboard/images/icon/avatar-big-01.jpg" alt=""/>
                    @endif
                </div>
                <h4 class="name">{{ Auth::user()->firstname.' '.Auth::user()->lastname }}</h4>
                <a href="{{route('logout')}}">Logout</a>
            </div>
        @endif
        <nav class="navbar-sidebar2">
            <ul class="list-unstyled navbar__list">
                <li class="has-sub {{ (Route::is('dashboard') || Route::is('myinstitution') || Route::is('map-my-student') ) ? 'active' : '' }}">
                    <a class="js-arrow" href="#">
                        <i class="fa fa-tachometer-alt"></i>Dashboard
                        <span class="navarrow">
                                    <i class="fas fa-angle-down"></i>
                                </span>
                    </a>
                    <ul class="navbar-mobile-sub__list list-unstyled js-sub-list" style="display:{{ (Route::is('dashboard') || Route::is('myinstitution') || Route::is('map-my-student') ) ? 'block' : 'none' }};">
                        <li class="{{ Route::is('dashboard') ? 'active' : '' }}">
                            <a href="{{route('dashboard')}}">
                                My Dashboard</a>
                        </li>
                        <li class="{{ Route::is('myinstitution') ? 'active' : '' }}">
                            <a href="{{route('myinstitution')}}">
                                My Institution Request</a>
                        </li>
                        {{--<li>--}}
                        {{--<a href="WE-Admin-Dashboard.html">--}}
                        {{--WE Admin Dashboard</a>--}}
                        {{--</li>--}}
                        {{--<li>
                            <a href="Map-Partners.html">
                              Map Partners</a>
                        </li>--}}
                        <li class="{{ Route::is('map-my-student') ? 'active' : '' }}">
                            <a href="{{route('map-my-student')}}">
                                Map My Students</a>
                        </li>
                    </ul>
                </li>
                <li class="{{ (Route::is('catalog-listing') ? 'active' : '') }}">
                    <a href="{{route('catalog-listing')}}">
                        <i class="fas fa-copy"></i>Catalog Management</a>
                </li>
                <li class="has-sub {{ (Route::is('student-list')  || Route::is('import-audit') || Route::is('leads') ) ? 'active' : '' }}">
                    <a class="js-arrow" href="#">
                        <i class="fa fa-graduation-cap" aria-hidden="true"></i>Student Management
                        <span class="navarrow">
                                    <i class="fas fa-angle-down"></i>
                                </span>
                    </a>
                    <ul class="navbar-mobile-sub__list list-unstyled js-sub-list" style="display:{{ (Route::is('student-list') || Route::is('import-audit') || Route::is('leads') ) ? 'block' : 'none' }};">
                        <li class="{{ Route::is('student-list') ? 'active' : '' }}">
                            <a href="{{route('student-list')}}">
                                Student Dashboard
                            </a>
                        </li>
                        <li class="{{ Route::is('import-audit') ? 'active' : '' }}">
                            <a href="{{route('import-audit')}}">
                                Student Import Audit
                            </a>
                        </li>
                        <li class="{{ Route::is('leads') ? 'active' : '' }}">
                            <a href="{{route('leads')}}">
                                Leads
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="has-sub {{ (Route::is('announcement') || Route::is('request-collateral') || Route::is('marketing-collateral') || Route::is('course-marketing-course-marketing-category') || Route::is('marketing-collateral-funding-sources') || Route::is('marketing-collateral-social-media') ) ? 'active' : '' }}">
                    <a class="js-arrow" href="#">
                        <i class="fa far fa-chart-bar" aria-hidden="true"></i>Marketing
                        <span class="navarrow">
                                    <i class="fas fa-angle-down"></i>
                                </span>
                    </a>
                    <ul class="navbar-mobile-sub__list list-unstyled js-sub-list" style="display:{{ (Route::is('announcement')  || Route::is('announcement') || Route::is('announcement') || Route::is('request-collateral') || Route::is('marketing-collateral') || Route::is('course-marketing-course-marketing-category') || Route::is('marketing-collateral-marketing-category') || Route::is('marketing-collateral-funding-sources') || Route::is('marketing-collateral-social-media')) ? 'block' : 'none' }};">
                        <li class="{{ Route::is('announcement/news') ? 'active' : '' }}">
                            <a href="{{route('announcement', ['announce_type' => 'news'])}}">
                                News
                            </a>
                        </li>
                        <li class="{{ Route::is('announcement/announcements') ? 'active' : '' }}">
                            <a href="{{route('announcement', ['announce_type' => 'announcements'])}}">
                                Announcements
                            </a>
                        </li>
                        <li class="{{ Route::is('announcement/updates') ? 'active' : '' }}">
                            <a href="{{route('announcement', ['announce_type' => 'updates'])}}">
                                Updates
                            </a>
                        </li>
                        <li>
                            <a href="{{route('top-selling-programs')}}">
                                My Top Selling Programs
                            </a>
                        </li>
                        <li class="{{ Route::is('request-collateral') ? 'active' : '' }}">
                            <a href="{{route('request-collateral')}}">
                                Request Collateral
                            </a>
                        </li>
                        <li class="{{ (Route::is('marketing-collateral') || Route::is('course-marketing-course-marketing-category') || Route::is('marketing-collateral-marketing-category') || Route::is('marketing-collateral-funding-sources') || Route::is('marketing-collateral-social-media')) ? 'active' : '' }}">
                            <a href="{{route('marketing-collateral')}}">
                                Marketing Collateral
                            </a>
                        </li>
                    </ul>
                </li>

                {{--<li>
                    <a href="Marketing.html">
                        <i class="far fa-chart-bar"></i>Marketing</a>
                </li>--}}
                <li class="has-sub {{ (Route::is('institute-profile')  || Route::is('my-profile') ) ? 'active' : '' }}">
                    <a class="js-arrow" href="#">
                        <i class="fas fa-address-book"></i>Partner Profile
                        <span class="navarrow">
                                    <i class="fas fa-angle-down"></i>
                                </span>
                    </a>
                    <ul class="list-unstyled navbar__sub-list js-sub-list" style="display:{{ (Route::is('institute-profile')  || Route::is('my-profile') ) ? 'block' : 'none' }};">
                        <li class="{{ Route::is('institute-profile') ? 'active' : '' }}">
                            <a href="{{route('institute-profile')}}">
                                My Institution Profile</a>
                        </li>
                        <li class="{{ Route::is('my-profile') ? 'active' : '' }}">
                            <a href="{{route('my-profile')}}">
                                My Profile</a>
                        </li>
                    </ul>
                </li>
                @if(Auth::user()->user_type == '3')
                    <li class="{{ (Route::is('catalog-listing') ? 'active' : '') }}">
                        <a href="{{ route('my-profile') }}">
                            <i class="fas fa-file"></i>My Profile</a>
                    </li>
                @endif
                <li class="has-sub {{ (Route::is('partner-users') || Route::is('we-users') || Route::is('configuration-email') || Route::is('system-email-logs') || Route::is('permissions-partners') ) ? 'active' : '' }}">
                    <a class="js-arrow" href="#">
                        <i class="far fa-user"></i>PAS Admin
                        <span class="navarrow">
                                    <i class="fas fa-angle-down"></i>
                                </span>
                    </a>
                    <ul class="list-unstyled navbar__sub-list js-sub-list" style="display:{{ (Route::is('partner-users') || Route::is('we-users') || Route::is('configuration-email') || Route::is('system-email-logs') || Route::is('permissions-partners') ) ? 'block' : 'none' }};">
                        <li class="{{ Route::is('partner-users') ? 'active' : '' }}">
                            <a href="{{route('partner-users')}}">
                                Partner Users List</a>
                        </li>
                        <li class="{{ Route::is('we-users') ? 'active' : '' }}">
                            <a href="{{route('we-users')}}">
                                WE Users List </a>
                        </li>
                        <li class="{{ Route::is('configuration-email') ? 'active' : '' }}">
                            <a href="{{route('configuration-email')}}">
                                Configuration Email</a>
                        </li>
                        <li class="{{ Route::is('system-email-logs') ? 'active' : '' }}">
                            <a href="{{route('system-email-logs')}}">
                                System Email Logs</a>
                        </li>
                        <li class="{{ Route::is('permissions-partners') ? 'active' : '' }}" style="display: none;">
                            <a href="Manage-Permissions-Partners.html">
                                Manage Permissions-Partners</a>
                        </li>
                    </ul>
                </li>
                <li class="has-sub {{ (Route::is('my-users') ) ? 'active' : '' }}">
                    <a class="js-arrow" href="#">
                        <i class="fa fa-users"></i>Partner Admin
                        <span class="navarrow">
                                    <i class="fas fa-angle-down"></i>
                                </span>
                    </a>
                    <ul class="list-unstyled navbar__sub-list js-sub-list" style="display:{{ (Route::is('my-users') ) ? 'block' : 'none' }};">
                        <li class="{{ Route::is('my-users') ? 'active' : '' }}">
                            <a href="{{route('my-users')}}">
                                My Users List</a>
                        </li>
                        <li style="display: none;">
                            <a href="Permission-Assignment.html">
                                Permission Assignment</a>
                        </li>
                    </ul>
                </li>


                <li class="has-sub {{ (Route::is('marketing-form') || Route::is('we-templates') || Route::is('course-marketing') || Route::is('course-marketing-category') || Route::is('funding-sources') || Route::is('social-media')) ? 'active' : '' }}">
                    <a class="js-arrow" href="#">
                        <i class="fas fa-list-alt" aria-hidden="true"></i>PAS CMS
                        <span class="navarrow">
                                    <i class="fas fa-angle-down"></i>
                                </span>
                    </a>
                    <ul class="navbar-mobile-sub__list list-unstyled js-sub-list" style="display:{{ (Route::is('marketing-form') || Route::is('we-templates') || Route::is('course-marketing') || Route::is('course-marketing-category') || Route::is('funding-sources') || Route::is('social-media') ) ? 'block' : 'none' }};">
                        <li class="{{ Route::is('marketing-form') ? 'active' : '' }}">
                            <a href="{{route('marketing-form')}}">
                                Partner Announcement
                            </a>
                        </li>
                        <li class="{{ (Route::is('we-templates') || Route::is('course-marketing') || Route::is('course-marketing-category') || Route::is('funding-sources') || Route::is('social-media')) ? 'active' : '' }}">
                            <a href="{{route('we-templates')}}">We Templates</a>
                        </li>
                        {{--<li>
                            <a href="#">
                                Collateral Requests
                            </a>
                        </li>--}}
                    </ul>
                </li>

            </ul>
        </nav>
    </div>
</aside>


<div class="modal" id="login-alert" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                Your session will end within a minute! Do you want to continue?
                <p style="text-align:center">
                    <i class="fas fa-stopwatch"></i> <span id="second-remaining" ></span>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal" data-action="yes">Yes</button>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" data-action="no">No</button>
            </div>

        </div>
    </div>
</div>