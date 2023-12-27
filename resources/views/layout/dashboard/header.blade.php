<header class="header-desktop">
                <div class="section__content">
                    <div class="container-fluid">
                        <div class="header-wrap">
                            
                            <button class="btn btn-default" id="toggle-menu">
                                <i class="fa fa-chevron-left" aria-hidden="true"></i>
                            </button>

                            <form class="form-header" id="partner_selection_form">
                                {{csrf_field()}}
                                <input type="hidden" name="partner_name" id="partner_name">
                                <select name="partner" id="partner_selection" class="form-control" data-url="{{route('set-partner')}}">
                                    @if(\App\Models\User::isPartner() || \App\Models\User::isMyUser() )
                                        @foreach (getPartners() as $partner)
                                            @if(Auth::user()->partner_id == $partner['id'])
                                                <option value="{{$partner['zoho_id'] }}" {{\App\Models\User::getPartnerDetail('zoho_id') == $partner['zoho_id'] ? 'selected':''}}>{{$partner['partner_name'] }}</option>
                                            @endif()
                                        @endforeach
                                    @else
                                        @foreach (getPartners() as $partner)
                                            <option value="{{$partner['zoho_id'] }}" {{\App\Models\User::getPartnerDetail('zoho_id') == $partner['zoho_id'] ? 'selected':''}}>{{$partner['partner_name'] }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </form>


                            @if(!empty(\App\Models\User::getPartnerDetail('hosted_site')))
                                <a href="{{\App\Models\User::getPartnerDetail('hosted_site')}}" target="_blank">My Hosted Site </a>
                            @else
                                <a href="#" target="_blank"></a>
                            @endif

                            <div class="header-button">
                                <div class="noti-wrap">
                                    <div class="noti__item">
                                        <a href="{{ route('partner-inquiry-tool') }}">
                                          <i class="fa fa-question-circle" aria-hidden="true"></i> 
                                        </a>  
                                    </div>
                                    <div class="noti__item js-item-menu">
                                        @php
                                        if(!empty(\App\Models\User::getPartnerDetail('logo'))){
                                            $partner_logo = env('S3_PATH').'partner/'.\App\Models\User::getPartnerDetail('logo');
                                        }else{
                                            $partner_logo = $CDN_URL.'dashboard/images/icon/logo.png';
                                        }
                                        $notifications = \App\Models\UserNotification::getUserNotifications('pas_marketing');
                                        //print_r($notifications);die;
                                        $announcements = $notifications['announcements'];
                                        $news = $notifications['news'];
                                        $updates = $notifications['updates'];
                                        $news_updates = array_merge($news, $updates);
                                        @endphp
                                        <i class="zmdi zmdi-comment-more"></i>
                                        @if(!empty(count($announcements)))
                                            <span class="quantity">{{count($announcements)}}</span>
                                        @endif

                                        <div class="mess-dropdown js-dropdown">
                                            <div class="mess__title">
                                                <p>You have {{ count($announcements) > 0 ? count($announcements):'no' }} new Announcements</p>
                                            </div>
                                            @if(!empty(count($announcements)))
                                                @foreach($announcements as $announcement)
                                                <div class="mess__item">

                                                    <div class="image img-cir img-40">
                                                        <img src="{{$partner_logo}}" alt="" />
                                                    </div>
                                                    <div class="content">
                                                        <p>{{$announcement['title']}}</p>
                                                        <span class="time">{{$announcement['updated_at']}}</span>
                                                    </div>
                                                </div>
                                                @endforeach
                                            <div class="mess__footer">
                                                <a href="{{route('announcement', ['announce_type' => 'announcements'])}}">View All Announcements</a>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="noti__item js-item-menu">
                                        <i class="zmdi zmdi-email"></i>
                                        @if(!empty(count($news_updates)))
                                        <span class="quantity">{{count($news_updates)}}</span>
                                        @endif
                                        <div class="email-dropdown js-dropdown">
                                            <div class="email__title">
                                                <p>You have {{ count($news_updates) > 0 ? count($news_updates):'no' }} News and Updates</p>
                                            </div>
                                            @if(!empty(count($news_updates)))
                                                @php
                                                $news_updates_split = array_slice($news_updates, 0,4);
                                                @endphp

                                                @foreach($news_updates_split as $news_update)
                                            <div class="email__item">
                                                <div class="image img-cir img-40">
                                                    <img src="{{$partner_logo}}" alt="" />
                                                </div>
                                                <div class="content">
                                                    <p>{{$news_update['title']}}</p>
                                                    <span class="time">{{$news_update['updated_at']}}</span>
                                                </div>
                                            </div>

                                                @endforeach

                                                <div class="email__footer d-flex1" style="width:100%;">
                                                    <a href="{{route('announcement', ['announce_type' => 'news'])}}" class="text-left" style="float: left;width: 50%;padding-left: 10px;">See All News</a>
                                                    <a href="{{route('announcement', ['announce_type' => 'updates'])}}" class="text-right" style="float: left;width: 50%;padding-right: 10px;">See All Updates</a>
                                                </div>


                                            @endif
                                        </div>
                                    </div>
                                    @if(Session::has('partner_detail'))
                                        <div class="noti__item js-item-menu">
                                        @php
                                            $status_change_notifications = \App\Models\UserNotification::getStatusChangesNotifications();
                                        $sts_notifications = array_merge($status_change_notifications['institute_request'], $status_change_notifications['collateral_request']);
                                        @endphp

                                        <i class="zmdi zmdi-notifications"></i>
                                        @if(count($sts_notifications) > 0)
                                            <span class="quantity">{{ count($sts_notifications) }}</span>
                                        @endif
                                        <div class="notifi-dropdown js-dropdown">
                                            <div class="notifi__title">
                                                <p>You have {{ count($sts_notifications) > 0 ? count($sts_notifications): 'no' }} Status changes/Update</p>
                                            </div>
                                            @if(count($sts_notifications) > 0)
                                                @foreach($sts_notifications as $sts_notification)
                                                <div class="notifi__item">
                                                    <div class="bg-c1 img-cir img-40">
                                                        <img src="{{$partner_logo}}" alt="" />
                                                    </div>
                                                    <div class="content">
                                                        <p>New status change to your {{$sts_notification['title']}}</p>
                                                        <span class="date">{{$sts_notification['updated_at']}}</span>
                                                    </div>
                                                </div>
                                                @endforeach

                                                <div class="notifi__footer">
                                                    <a href="{{route('myinstitution')}}">See All  Status changes and Updates</a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                <div class="account-wrap">
                                    <div class="account-item clearfix js-item-menu">
                                        <div class="image">
                                        @if(Auth::user()->photo != '')
                                            <img src="{{env('S3_PATH')}}partner/{{Auth::user()->photo}}" alt="{{ Auth::user()->firstname.' '.Auth::user()->lastname }}"/>
                                        @else
                                            <img src="{{$CDN_URL}}dashboard/images/icon/avatar-big-01.jpg" alt="{{ Auth::user()->firstname.' '.Auth::user()->lastname }}"/>
                                        @endif
                                        </div>
                                        <div class="content">
                                            <a class="js-acc-btn" href="#" title="{{ Auth::user()->firstname.' '.Auth::user()->lastname}}">{{ substr(Auth::user()->firstname.' '.Auth::user()->lastname, 0, 9) }}</a>
                                        </div>
                                        <div class="account-dropdown js-dropdown">
                                            <div class="info clearfix">
                                                <div class="image">
                                                    <a href="#">
                                                    @if(Auth::user()->photo != '')
                                                        <img src="{{env('S3_PATH')}}partner/{{Auth::user()->photo}}" alt="{{ Auth::user()->firstname.' '.Auth::user()->lastname }}"/>
                                                    @else
                                                        <img src="{{$CDN_URL}}dashboard/images/icon/avatar-big-01.jpg" alt="{{ Auth::user()->firstname.' '.Auth::user()->lastname }}"/>
                                                    @endif
                                                    </a>
                                                </div>
                                                <div class="content">
                                                    <h5 class="name">
                                                        <a href="#">{{ Auth::user()->firstname.' '.Auth::user()->lastname }}</a>
                                                    </h5>
                                                    <span class="email">{{ Auth::user()->email }}</span>
                                                </div>
                                            </div>
                                            <div class="account-dropdown__body">
                                                <div class="account-dropdown__item">
                                                    <a href="{{route('my-profile')}}">
                                                        <i class="zmdi zmdi-account"></i>Account</a>
                                                </div>
                                                <div class="account-dropdown__item" style="display: none;">
                                                    <a href="#">
                                                        <i class="zmdi zmdi-settings"></i>Setting</a>
                                                </div>
                                            </div>
                                            <div class="account-dropdown__footer">
                                                <a href="{{route('logout')}}">
                                                    <i class="zmdi zmdi-power"></i>Logout</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>