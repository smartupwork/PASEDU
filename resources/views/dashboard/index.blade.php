<?php
use \App\Models\UserAccess;
?>

@extends('layout.main')
@section('content')

    <div class="section__content section__content--p30">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-9">
                                <h2><?= \App\Models\User::getPartnerDetail('partner_name')?></h2>
                            </div>
                            @if(UserAccess::hasAccess(UserAccess::STATS_ACCESS, 'add'))
                            <div class="col-md-3 text-right">
                                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#mediumModal">
                                    <i class="fa fa-list-ul" aria-hidden="true"></i> More item</button>
                            </div>
                            @endif
                        </div>

                        {{--@if(!empty($error_msg))
                        <div class="row m-t-25">
                            <div class="alert alert-danger" role="alert">
                                    {{$error_msg}}
                            </div>
                        </div>
                        @endif--}}

                        {{--<div class="row m-t-25" id="iframe-report">
                            <iframe frameborder=0 width="1000" height="160" src="https://analytics.zoho.com/open-view/2392301000000197631/a29111fb27e6a8a80eb02b8797ba67b1"></iframe>
                        </div>--}}

                        @if(UserAccess::hasAccess(UserAccess::STATS_ACCESS, 'view'))

                        @if(Auth::user()->roleid != \App\Models\User::ROLE_SALES_TEAM)
                            <div class="row m-t-25">
                        <?php $count = 1; ?>

                        @if($highlight_reports)
                            @foreach($highlight_reports as $highlight_report)
                            @if(in_array($highlight_report['slug'], $highlight_report_selected))
                            <div class="col-sm-6 col-lg-3">
                                <div class="overview-item overview-item--c{{$count++}}">
                                    <div class="overview__inner">
                                        <div class="overview-box media d-flex">
                                             <div class="text">
                                                <h2>{{$highlight_report['value']}}</h2>
                                                <span>{{$highlight_report['label']}}</span>
                                            </div>
                                            <div class="icon align-self-center">
                                                <i class="zmdi {{$highlight_report['icon_class']}}"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                                @endif
                            @endforeach
                        @endif

                    </div>
                        @endif

                        <div class="row m-t-25 pas-badges">
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-header">
                                        <strong class="card-title mb-3">Military Friendly</strong>
                                    </div>
                                    @php
                                    $mcaa_text = (\App\Models\User::getPartnerDetail('mycaa') == 0 ? 'YOU ARE NOT A MILITARY FRIENDLY PARTNER':'YOU ARE A MILITARY FRIENDLY PARTNER');
                                    $top_10_text = (array_key_exists(\App\Models\User::getPartnerDetail('id'), $top_sellers_ids) ? 'YOU ARE A TOP 10 SELLER':'YOU ARE NOT A TOP 10 SELLER');
                                    $wioa_text = (\App\Models\User::getPartnerDetail('wia') == 0 ? 'YOU ARE NOT A WIOA APPROVED PARTNER':'YOU ARE A WIOA APPROVED PARTNER');
                                    $wevip_text = (array_key_exists(\App\Models\User::getPartnerDetail('id'), $top_sellers_ids) && $top_sellers_ids[\App\Models\User::getPartnerDetail('id')] > 0 ? 'YOU ARE A WE VIP':'YOU ARE NOT A WE VIP');
                                    @endphp

                                    <div class="card-body">
                                        <div class="mx-auto d-block">
                                            <img class="rounded-circle mx-auto d-block {{ (\App\Models\User::getPartnerDetail('mycaa') == 0 ? 'badges-img':'') }}" src="{{ $CDN_URL}}dashboard/images/icon/military-icon1.png" alt="{{ $mcaa_text }}" title="{{ $mcaa_text }}">
                                            <div class="m-t-25 text-sm-center">Recognized for having World Education programs listed on the AI Portal.</div>
                                        </div>
                                        <div class="card-text text-sm-center">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-header">
                                        <strong class="card-title mb-3">Top 10 Sales Partner</strong>
                                    </div>
                                    <div class="card-body">
                                        <div class="mx-auto d-block">
                                            <img class="rounded-circle mx-auto d-block {{!array_key_exists(\App\Models\User::getPartnerDetail('id'), $top_sellers_ids) ? 'badges-img': ''}}" src="{{ $CDN_URL}}dashboard/images/icon/partner1.png" alt="{{$top_10_text}}" title="{{$top_10_text}}">
                                            <div class="m-t-25 text-sm-center">Recognized for being a top ten sales partner with World Education.</div>
                                        </div>
                                        <div class="card-text text-sm-center"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-header">
                                        <strong class="card-title mb-3">WIOA Approved</strong>
                                    </div>
                                    <div class="card-body">
                                        <div class="mx-auto d-block">
                                            <img class="rounded-circle mx-auto d-block {{ (\App\Models\User::getPartnerDetail('wia') == 0 ? 'badges-img':'') }}" src="{{ $CDN_URL}}dashboard/images/icon/approved1.png" alt="{{$wioa_text}}" title="{{$wioa_text}}">
                                            <div class="m-t-25 text-sm-center">Recognized for having at least one WE CTP approved for WIOA on the ETPL.</div>
                                        </div>
                                        <div class="card-text text-sm-center">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-header">
                                        <strong class="card-title mb-3">WE VIP</strong>
                                    </div>
                                    <div class="card-body">
                                        <div class="mx-auto d-block">
                                            <img class="rounded-circle mx-auto d-block {{ (array_key_exists(\App\Models\User::getPartnerDetail('id'), $top_sellers_ids) && $top_sellers_ids[\App\Models\User::getPartnerDetail('id')] > 0 ? '':'badges-img')}}" src="{{ $CDN_URL}}dashboard/images/icon/wvip.png" alt="{{$wevip_text}}" title="{{$wevip_text}}">
                                            <div class="m-t-25 text-sm-center">Recognized for having MyCAA WIOA, and 10+ annual enrollments with World Education.</div>
                                        </div>

                                        <div class="card-text text-sm-center"> </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @include('layout.dashboard.footer')
                    </div>
                </div>

    <div class="modal fade" id="mediumModal" tabindex="-1" role="dialog" aria-labelledby="mediumModalLabel" aria-hidden="true">
        <form name="frmname" id="frmid" method="post" action="{{ route('update-dashboard')}}" >
            {{csrf_field()}}
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="mediumModalLabel">More Items</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body check-element">
                            <p id="alert-error" class="alert alert-danger" role="alert"></p>
                            <div class="row m-t-25">

                                @if($highlight_reports)
                                    @foreach($highlight_reports as $key => $highlight_report)
                                <div class="col-sm-6 col-lg-4">
                                    <input id="lists-{{$key}}" type="checkbox" {{in_array($highlight_report['slug'], $highlight_report_selected) ? 'checked':''}} name="report[]" value="{{$highlight_report['slug']}}" class="report-list" />
                                    <label for="lists-{{$key}}">{{$highlight_report['label']}} {{$highlight_report['value']}}</label>
                                </div>
                                    @endforeach
                                @endif

                            </div>
						</div>
						<div class="modal-footer">
							<button type="reset" class="btn btn-secondary btn-sm" data-dismiss="modal" onclick="javascript:window.location.reload();">Cancel</button>
							<button type="submit" id="submit-btn" class="btn btn-primary btn-sm">Save</button>
						</div>
					</div>
				</div>
        </form>
    </div>
@section('myCssFiles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/css/bootstrap-datepicker.css" rel="stylesheet" media="all">
    <style>
        .typeahead li a{
            font-size: 10px; white-space: nowrap; height: 10px;
        }
        /*.modal-backdrop{display: none;}

        label{
            width: 30px;
            height: 22px;
            background: none;
            color: #6c757d;
            border: 1px solid #6c757d;
        }*/
    </style>
@stop

@section('myJsFile')

    <script src="{{$CDN_URL}}js/jquery.form.js"></script>

    <script>
        $(document).ready(function(){
            /*$(".pas-badges img").click(function(){
             $(this).toggleClass("badges-img");
            });*/
            $("#alert-error").hide();
            $("input[name^='report']").change(function () {
                var maxAllowed = 4;
                var cnt = $("input[name^='report']:checked").length;
                if (cnt > maxAllowed) {
                    $(this).prop("checked", "");
                    document.getElementById("alert-error").innerHTML = 'You can select exactly ' + maxAllowed + ' items!!';
                    $("#alert-error").show();
                }
            });

            $('#frmid').ajaxForm({
                beforeSubmit: function() {
                    $("#submit-btn").html('Processing...').attr('disabled', true);
                    var cnt = $("input[name^='report']:checked").length;
                    if(cnt != 4){
                        document.getElementById("alert-error").innerHTML = 'You can select exactly 4 items!!';
                        $("#alert-error").show();
                        $("#submit-btn").html('Save').attr('disabled', false);
                        return false;
                    }
                },
                //dataType: 'json',
                success: function(data) {
                    $("#submit-btn").html('Save').attr('disabled', false);
                    $('input, select').removeClass('is-invalid');
                    if(data.status == "success"){
                        alert(data.msg);
                        window.location.reload();
                    }else{
                        alert(data.msg);
                    }
                },
                complete: function(){
                    $("#submit-btn").html('Save').attr('disabled', false);
                    //$('input, select').removeClass('is-invalid');
                },
                error: function(xhr){
                    if(xhr.status === 419){
                        window.location.reload();
                    }else{
                        $("#submit-btn").html('Save').attr('disabled', false);
                        alert(xhr.responseJSON.message);
                    }
                }
            });

        });

    </script>
@stop
@stop
