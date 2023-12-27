@php
    $user_menu = \App\Models\UserMenu::getUserMenu();
@endphp

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

                        @foreach($user_menu as $menu)
                        <li class="has-sub">
                            <a class="js-arrow" href="{{$menu['route'] != '#' ? route($menu['route']):'#'}}">
                                <i class="{{$menu['icon']}}"></i>{{$menu['label']}}
                                @if(isset($menu['sub_menu']) && count($menu['sub_menu']) > 0)
                                    <span class="navarrow">
                                        <i class="fas fa-angle-down"></i>
                                    </span>
                                @endif
                            </a>
                            @if(isset($menu['sub_menu']) && count($menu['sub_menu']) > 0)
                                <ul class="navbar-mobile-sub__list list-unstyled js-sub-list">
                                    @foreach($menu['sub_menu'] as $sub_menu)
                                        @php
                                            if($sub_menu['route'] == '#'){
                                                $child_url = '#';
                                            }else{
                                                if(isset($sub_menu['params'])){
                                                    $child_url = route($sub_menu['route'], $sub_menu['params']);
                                                }else{
                                                    $child_url = route($sub_menu['route']);
                                                }
                                            }
                                        @endphp
                                    <li>
                                        <a href="{{$child_url}}">
                                            {{$sub_menu['label']}}</a>
                                    </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                        @endforeach

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
                        @foreach($user_menu as $menu)
                            {{--@php
                                if(isset($menu['sub_menu'])){
                                    if(isset($sub_menu['params'])){
                                        $child_url = route($sub_menu['route'], $sub_menu['params']);
                                    }else{
                                        $child_url = route($sub_menu['route']);
                                    }
                                }
                            @endphp--}}
                            <li class="has-sub {{ $menu['class'] }}">
                                <a class="js-arrow" href="{{$menu['route'] != '#' ? route($menu['route']):'#'}}">
                                    <i class="fa {{$menu['icon']}}"></i>{{$menu['label']}}
                                    @if(isset($menu['sub_menu']) && count($menu['sub_menu']) > 0)
                                    <span class="navarrow">
                                        <i class="fas fa-angle-down"></i>
                                    </span>
                                    @endif
                                </a>
                                @if(isset($menu['sub_menu']) && count($menu['sub_menu']) > 0)
                                <ul class="navbar-mobile-sub__list list-unstyled js-sub-list" style="display:{{ $menu['class'] == 'active' ? 'block':'none'}};">
                                    @foreach($menu['sub_menu'] as $sub_menu)
                                        @php
                                            if($sub_menu['route'] == '#'){
                                                $child_url = '#';
                                            }else{
                                                if(isset($sub_menu['params'])){
                                                    $child_url = route($sub_menu['route'], $sub_menu['params']);
                                                }else{
                                                    $child_url = route($sub_menu['route']);
                                                }
                                            }
                                        @endphp
                                    <li class=" {{ $sub_menu['class'] }}">
                                        <a href="{{$child_url}}">{{$sub_menu['label']}}</a>
                                    </li>
                                    @endforeach
                                </ul>
                                @endif
                            </li>
                        @endforeach
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