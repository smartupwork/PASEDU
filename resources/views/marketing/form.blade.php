<?php
use \App\Models\UserAccess;
?>
@extends('layout.main')
@section('content')

    <div class="section__content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            Partner Announcements
                        </div>
                        <ul id="myTab2" role="tablist" class="nav nav-tabs nav-pills with-arrow lined flex-column flex-sm-row text-center">
                            <li class="nav-item flex-sm-fill">
                                <a id="home2-tab" data-toggle="tab" href="#home2" role="tab" aria-controls="home2" aria-selected="true" class="nav-link rounded-0 {{Session::get('marketing-active-tab') == 'news' ? 'active':''}}">News</a>
                            </li>
                            <li class="nav-item flex-sm-fill">
                                <a id="profile2-tab" data-toggle="tab" href="#profile2" role="tab" aria-controls="profile2" aria-selected="false" class="nav-link rounded-0 {{Session::get('marketing-active-tab') == 'announcements' ? 'active':''}}">Announcements</a>
                            </li>
                            <li class="nav-item flex-sm-fill">
                                <a id="contact2-tab" data-toggle="tab" href="#contact2" role="tab" aria-controls="contact2" aria-selected="false" class="nav-link rounded-0 {{Session::get('marketing-active-tab') == 'updates' ? 'active':''}}">Updates</a>
                            </li>
                        </ul>
                        <div id="myTab2Content" class="tab-content news-update">
                            <div id="home2" role="tabpanel" aria-labelledby="home-tab" class="tab-pane fade px-4 py-5 {{Session::get('marketing-active-tab') == 'news' ? 'show active':''}}">
                                <form name="news-from" id="news-from" action="{{route('marketing-store')}}" method="post" enctype="multipart/form-data">
                                    {{@csrf_field()}}
                                    <input type="hidden" name="market_type" value="news">
                                <div class="card">
                                    {{--<img src="images/html-editor1.jpg" class="img-fluid" alt="">--}}
                                    <div class="card-body col-md-10 mx-auto">

                                        <?php $counter = 0;?>
                                        @foreach(\App\Models\Marketing::getNewsTitles() as $key => $title)
                                        <div class="row">
                                            <div class="col-md-11">
                                                <label for="{{$key}}" class="form-control-label">{{ $title }}</label>
                                                @if($data_news && isset($data_news[$key]))
                                                    <input type="hidden" name="news[{{$counter}}][id]" value="{{$data_news[$key]['id']}}">
                                                @endif
                                                <input type="hidden" value="{{ $title }}" name="news[{{$counter}}][title]">
                                                <input type="hidden" value="news" name="news[{{$counter}}][marketing_type]">
                                                <input type="hidden" value="{{$key}}" name="news[{{$counter}}][slug]">
                                                <div class="input-group mb-3 inline-ckeditor-cust">
                                                    <div class="custom-file" style="height: auto; scroll: auto;">
                                                        <textarea id="news-{{$key}}" name="news[{{$counter}}][description]" class="form-control pas-ckeditor"><?= (!empty($data_news) && isset($data_news[$key])) ? $data_news[$key]['description']:''?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="input-group-append switchbutton col-md-1 pl-0">
                                                <input type="checkbox" class="switch-toggle" id="status-{{$key}}" name="news[{{$counter}}][status]" value="1" <?= (!empty($data_news) && isset($data_news[$key]) && $data_news[$key]['status'] == \App\Utility::STATUS_ACTIVE) ? 'checked':''?>>

                                                <input type="hidden" value="{{isset($data_news[$key]) ? $data_news[$key]['status']:null}}" name="news[{{$counter}}][old_status]">

                                            </div>
                                        </div>
                                                <?php $counter++;?>
                                        @endforeach

                                    </div>
                                    @if(UserAccess::hasAccess(UserAccess::PARTNER_ANNOUNCEMENT_ACCESS, 'add'))
                                    <div class="card-footer">
                                        <div class="row">
                                            <div class="col-md-12">
                                                {{--<button type="button" onclick="searchform.reset();" class="btn btn-secondary btn-sm float-right ml-1">Cancel </button>--}}
                                                <button type="submit" class="btn btn-primary btn-sm  float-right">Save
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                </form>
                            </div>
                            <div id="profile2" role="tabpanel" aria-labelledby="profile-tab" class="tab-pane fade px-4 py-5 {{Session::get('marketing-active-tab') == 'announcements' ? 'show active':''}}">

                                <form name="announcements-from" id="announcements-from" action="{{route('marketing-store')}}" method="post" enctype="multipart/form-data">
                                    {{@csrf_field()}}
                                    <input type="hidden" name="market_type" value="announcements">
                                    <div class="card">
                                        {{--<img src="images/html-editor1.jpg" class="img-fluid" alt="">--}}
                                        <div class="card-body col-md-10 mx-auto">

                                            <?php $counter = 0;
                                            /*if(isset($all_data['announcements']) && count($all_data['announcements']) > 0){
                                                $total_inputs -= count($all_data['announcements']);
                                            }*/
                                            ?>
                                            @for($i = 0; $i < 12; $i++)
                                                <div class="row">
                                                    <div class="col-md-11">
                                                        @if(isset($all_data['announcements'][$counter]))
                                                            <input type="hidden" name="announcements[{{$counter}}][id]" value="{{$all_data['announcements'][$counter]['id']}}">
                                                        @endif

                                                    <div class="form-group">
                                                        <input type="text" id="title-{{$i}}" placeholder="Title" name="announcements[{{$counter}}][title]" class="form-control" value="<?= (!empty($all_data['announcements']) && isset($all_data['announcements'][$counter])) ? $all_data['announcements'][$counter]['title']:''?>">
                                                    </div>

                                                        <input type="hidden" value="announcements" name="announcements[{{$counter}}][marketing_type]">
                                                        <div class="input-group mb-3 inline-ckeditor-cust">
                                                            <div class="custom-file" style="height: auto; scroll: auto;">
                                                                <textarea id="announcements-{{$counter}}" name="announcements[{{$counter}}][description]" class="form-control pas-ckeditor"><?= (!empty($all_data['announcements']) && isset($all_data['announcements'][$counter])) ? $all_data['announcements'][$counter]['description']:''?></textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="input-group-append switchbutton col-md-1 pl-0">
                                                        <input type="checkbox" class="switch-toggle" id="status-{{$counter}}" name="announcements[{{$counter}}][status]" value="1" <?= (!empty($all_data['announcements']) && isset($all_data['announcements'][$counter]) && $all_data['announcements'][$counter]['status'] == \App\Utility::STATUS_ACTIVE) ? 'checked':''?>>
                                                    </div>
                                                </div>
                                                <?php $counter++;?>
                                            @endfor

                                        </div>
                                        @if(UserAccess::hasAccess(UserAccess::PARTNER_ANNOUNCEMENT_ACCESS, 'add'))
                                        <div class="card-footer">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    {{--<button type="button" onclick="searchform.reset();" class="btn btn-secondary btn-sm float-right ml-1">Cancel </button>--}}
                                                    <button type="submit" class="btn btn-primary btn-sm  float-right">Save
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </form>

                            </div>
                            <div id="contact2" role="tabpanel" aria-labelledby="contact-tab" class="tab-pane fade px-4 py-5 {{Session::get('marketing-active-tab') == 'updates' ? 'show active':''}}">
                                <form name="updates-from" id="updates-from" action="{{route('marketing-store')}}" method="post" enctype="multipart/form-data">
                                    {{@csrf_field()}}
                                    <input type="hidden" name="market_type" value="updates">
                                    <div class="card">

                                    <div class="card-body col-md-10 mx-auto">

                                        <?php $counter = 0;?>
                                        @foreach(\App\Models\Marketing::getUpdatesTitles() as $key => $title)
                                            <div class="row">
                                                <div class="col-md-11">
                                                    <label for="{{$key}}" class="form-control-label">{{ $title }}</label>
                                                    @if($data_updates && isset($data_updates[$key]))
                                                        <input type="hidden" name="updates[{{$counter}}][id]" value="{{$data_updates[$key]['id']}}">
                                                    @endif
                                                    <input type="hidden" value="{{ $title }}" name="updates[{{$counter}}][title]">
                                                    <input type="hidden" value="updates" name="updates[{{$counter}}][marketing_type]">
                                                    <input type="hidden" value="{{$key}}" name="updates[{{$counter}}][slug]">
                                                    <div class="input-group mb-3 inline-ckeditor-cust">
                                                        <div class="custom-file" style="height: auto; scroll: auto;">
                                                            <textarea id="updates-{{$key}}" name="updates[{{$counter}}][description]" class="form-control pas-ckeditor"><?= (!empty($data_updates) && isset($data_updates[$key])) ? $data_updates[$key]['description']:''?></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="input-group-append switchbutton col-md-1 pl-0">
                                                    <input type="checkbox" class="switch-toggle" id="status-{{$key}}" name="updates[{{$counter}}][status]" value="1" <?= (!empty($data_updates) && isset($data_updates[$key]) && $data_updates[$key]['status'] == \App\Utility::STATUS_ACTIVE) ? 'checked':''?>>
                                                </div>
                                            </div>
                                            <?php $counter++;?>
                                        @endforeach

                                    </div>
                                    @if(UserAccess::hasAccess(UserAccess::PARTNER_ANNOUNCEMENT_ACCESS, 'add'))
                                    <div class="card-footer">
                                        <div class="row">
                                            <div class="col-md-12">
                                                {{--<button type="reset" class="btn btn-secondary btn-sm float-right ml-1">Cancel </button>--}}
                                                <button type="submit" class="btn btn-primary btn-sm  float-right">Save
                                                </button>

                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@section('myCssFiles')
    <style>
        .inline-ckeditor-cust p{ width: 100px%;margin-left:10px;}
        .inline-ckeditor-cust{
            border: 1px solid #e2e5e7;
        }
        .cke_textarea_inline{ width: 600px;}
    </style>
    @stop
@section('myJsFile')
    <script src="{{$CDN_URL}}js/jquery.form.js"></script>

    <script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
    <script>

        //$(window).load(function (){
            $('.pas-ckeditor').each(function(e){
                CKEDITOR.inline( this.id, { customConfig: '/ckeditor/config-basic.js' });
            });
        //});


        //CKEDITOR.replace( 'message' );

        $('#news-from, #announcements-from, #updates-from').ajaxForm({
            beforeSubmit: function() {
                $("#btnAdd").html('Processing...')//.attr('disabled', true);
            },
            dataType: 'json',
            success: function(data) {
                $("#btnAdd").html('Save')//.attr('disabled', false);
                if(data.status == "success"){
                    alert(data.msg);
                    window.location.reload();
                }else{
                    alert(data.msg);
                }
            },
            error: function(xhr){
                if(xhr.status === 419){
                    window.location.reload();
                }else{
                    $("#btnAdd").html('Save');
                    alert(xhr.responseJSON.message);
                }
            }
        });
    </script>
    @stop
@stop

