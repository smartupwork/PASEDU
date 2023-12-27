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
                        <div class="card-header">Social Media</div>
                        <div class="card-body card-block m-3 psp-temp">
                            @if(UserAccess::hasAccess(UserAccess::WE_TEMPLATE_ACCESS, 'add'))
                            <form name="frmname" id="frmid" action="{{route('store-template')}}" method="post" enctype="multipart/form-data">
                                {{csrf_field()}}
                                <div class="mx-auto col-md-6">
                                    <div class="form-group">
                                        <select name="category" id="category" class="form-control">
                                            <option value="">Select Category</option>
                                            @foreach($categories as $category)
                                                <option value="{{$category->id}}" data-group-type="{{$category->group_type}}">{{$category->category_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="custom-file mb-3">
                                        <input type="file" class="custom-file-input" id="media_file" name="media_file">
                                        <label class="custom-file-label" for="media_file">Choose Template</label>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="reset" class="btn btn-secondary btn-sm float-right ml-1"> Reset</button>
                                        <button type="submit" class="btn btn-primary btn-sm  float-right" id="submit-btn">Submit</button>
                                    </div>
                                </div>

                            </form>
                            @endif
                            <div class="mx-auto col-md-12 download-img">
                                <p class="mb-3">
                                    These images and video may be downloaded and used on social media to promote
                                    your courses! Click on the links below to download. Course specific images will take
                                    you to their Course Marketing page where you may download them branded with World Education Logos, or without.
                                    so you may add your own logos. For social media images and video for funding sources such as MYCAA, WIOA, or Vocational
                                    Rehabilitation.
                                </p>
                                <div class="row text-center mb-4">

                                    @if(isset($media_files_group['Image']))
                                        @foreach($media_files_group['Image'] as $media_file)
                                            <div class="col-md-4">
                                                <h5 class="mb-3">{{$media_file->category_name}}</h5>
                                                <div class="mb-4">
                                                    <img src="{{env('S3_PATH')}}we-templates/{{ $media_file->media_file}}" alt="{{$media_file->category_name}}" title="{{$media_file->category_name}}">
                                                    @if(UserAccess::hasAccess(UserAccess::WE_TEMPLATE_ACCESS, 'download'))
                                                    <p>
                                                        <a href="{{env('S3_PATH')}}we-templates/{{ $media_file->media_file}}" target="_blank">
                                                            <i class="fa fa-download" aria-hidden="true"></i> View &amp; Download
                                                        </a>
                                                    </p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif

                                </div>
                                @if(isset($media_files_group['Video']))
                                <h2 class="mb-3">Video</h2>
                                <div class="row text-center mb-4">
                                    @foreach($media_files_group['Video'] as $media_file)
                                        <div class="col-md-4">
                                                <h5 class="mb-3">{{str_replace('Video', '', $media_file->category_name)}}</h5>
                                    <div class="mb-4">
                                        <img src="{{env('S3_CONTENT_PATH')}}vposter/{{ $media_file->media_name}}" alt="{{$media_file->category_name}}" title="{{$media_file->category_name}}">
                                        @if(UserAccess::hasAccess(UserAccess::WE_TEMPLATE_ACCESS, 'download'))
                                        <p>
                                            <a href="{{env('S3_PATH')}}we-templates/{{ $media_file->media_file}}" target="_blank">
                                                <i class="fa fa-download" aria-hidden="true"></i> View &amp; Download
                                            </a>
                                            <span class="vdotooltip"> <i class="fa fa-info-circle" aria-hidden="true"></i>
                                                                <span class="vdotooltiptext">To download the video, Please press Ctrl + S</span>
                                                            </span>

                                        </p>
                                        @endif
                                    </div>
                                </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-12">
                                    <a href="{{route('we-templates')}}" class="btn btn-secondary btn-sm">Back</a>
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
