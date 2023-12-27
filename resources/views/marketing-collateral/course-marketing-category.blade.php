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
                        <div class="card-header">{{$parent_category->category_name}}</div>
                        <div class="card-body card-block m-3 psp-temp">
                            <div class="mx-auto col-md-10 download-img">
                                <p class="mb-3">
                                    Click on the links below to download course fliers or social media images. You may choose files that are branded with World Education Logos, or  unbranded, so that you may add your own logos!
                                </p>
                                <div class="row">
                                    <div class="col-md-6 text-center">
                                        @if(isset($media_files_group['Branded']))
                                            @foreach($media_files_group['Branded'] as $media_file)
                                            <div class="mb-4">
                                                <h2 class="mb-3">{{$media_file->category_name}}</h2>
                                                <img src="{{env('S3_PATH')}}we-templates/{{ $media_file->media_file}}" alt="{{$media_file->category_name}}" title="{{$media_file->category_name}}">
                                                @if(UserAccess::hasAccess(UserAccess::MARKETING_COLLATERAL_ACCESS, 'download'))
                                                <p>
                                                    <a href="{{env('S3_PATH')}}we-templates/{{ $media_file->media_file}}" target="_blank">
                                                        <i class="fa fa-download" aria-hidden="true"></i> View &amp; Download
                                                    </a>
                                                </p>
                                                @endif
                                            </div>
                                            @endforeach
                                        @endif
                                        {{--<div class="mb-4">--}}
                                            {{--<h5 class="mb-3">Social Media</h5>--}}
                                            {{--<img src="images/temp/pastry_social_branded.jpg" alt="">--}}
                                            {{--<p>--}}
                                                {{--<a href="images/temp/pastry_social_branded.jpg" target="_blank">--}}
                                                    {{--<i class="fa fa-download" aria-hidden="true"></i> View &amp; Download--}}
                                                {{--</a>--}}
                                            {{--</p>--}}
                                        {{--</div>--}}
                                    </div>
                                    <div class="col-md-6 text-center">
                                        @if(isset($media_files_group['Unbranded']))
                                            @foreach($media_files_group['Unbranded'] as $media_file)
                                                <div class="mb-4">
                                                    <h2 class="mb-3">{{$media_file->category_name}}</h2>
                                                    <img src="{{env('S3_PATH')}}we-templates/{{ $media_file->media_file}}" alt="{{$media_file->category_name}}" title="{{$media_file->category_name}}">
                                                    @if(UserAccess::hasAccess(UserAccess::MARKETING_COLLATERAL_ACCESS, 'download'))
                                                    <p>
                                                        <a href="{{env('S3_PATH')}}we-templates/{{ $media_file->media_file}}" target="_blank">
                                                            <i class="fa fa-download" aria-hidden="true"></i> View &amp; Download
                                                        </a>
                                                    </p>
                                                    @endif
                                                </div>
                                            @endforeach
                                        @endif
                                        {{--<div class="mb-4">
                                            <h2 class=" mb-3">Unbranded</h2>
                                            <img src="images/temp/pastry_flier_unbranded.jpg" alt="">
                                            <p>
                                                <a href="images/temp/pastry_flier_unbranded.jpg" target="_blank">
                                                    <i class="fa fa-download" aria-hidden="true"></i> View &amp; Download
                                                </a>
                                            </p>
                                        </div>
                                        <div class="mb-4">
                                            <h5 class="mb-3">Social Media</h5>
                                            <img src="images/temp/pastry_social_unbranded.jpg" alt="">
                                            <p>
                                                <a href="images/temp/pastry_social_unbranded.jpg" target="_blank">
                                                    <i class="fa fa-download" aria-hidden="true"></i> View &amp; Download
                                                </a>
                                            </p>
                                        </div>--}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-12">
                                    <a href="{{route('marketing-collateral-marketing-category', ['course_marketing' => 'course-marketing'])}}" class="btn btn-secondary btn-sm">Back to Course Marketing</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@section('myCssFiles')

@stop

@section('myJsFile')

    <script src="{{$CDN_URL}}js/jquery.form.js"></script>

    <script>
        $(document).ready(function(){

            /*$('#category').change(function(){
                alert($(this).find(':selected').data('group-type'));
            });*/
            $('#frmid').ajaxForm({
                beforeSubmit: function() {
                    $("#submit-btn").html('Processing...').attr('disabled', true);
                },
                //dataType: 'json',
                success: function(data) {
                    $("#submit-btn").html('Submit').attr('disabled', false);
                    $('input, select').removeClass('is-invalid');
                    if(data.status === "success"){
                        alert(data.msg);
                        window.location.reload();
                    }else{
                        alert(data.msg);
                    }
                },
                complete: function(){
                    $("#submit-btn").html('Submit').attr('disabled', false);
                    //$('input, select').removeClass('is-invalid');
                },
                error: function(xhr){
                    if(xhr.status === 419){
                        window.location.reload();
                    }else{
                        $("#submit-btn").html('Submit').attr('disabled', false);
                        alert(xhr.responseJSON.message);
                    }
                }
            });

        });

    </script>
@stop
@stop
