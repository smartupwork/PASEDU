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
                        <div class="card-header">Funding Source</div>
                        <div class="card-body card-block m-3 psp-temp">
                            <div class="mx-auto col-md-10">
                                <p class="mb-3">
                                    These are marketing materials to call awareness to the funding sources that you accept. Click on the links to download them.
                                </p>

                                @if(count($media_files_group) > 0)

                                    @foreach($media_files_group as $group_name => $group_templates)
                                    <h2 class="mb-3">{{$group_name}}</h2>

                                    <div class="row text-center mb-4">
                                        @foreach($group_templates as $media_file)
                                        <div class="col-md-4">
                                            @if($group_name == 'Vocational Rehabilitation')
                                                <h5 class="mb-3">{{str_replace('VR', '', $media_file->category_name)}}</h5>
                                            @else
                                                <h5 class="mb-3">{{str_replace($group_name, '', $media_file->category_name)}}</h5>
                                            @endif
                                            <div class="mb-4">
                                                <img src="{{env('S3_PATH')}}we-templates/{{ $media_file->media_file}}" alt="{{$media_file->category_name}}" title="{{$media_file->category_name}}">
                                                @if(UserAccess::hasAccess(UserAccess::MARKETING_COLLATERAL_ACCESS, 'download'))
                                                <p>
                                                    @if(isset($media_file->video_file) && $media_file->video_file != '')
                                                    <a href="{{env('S3_PATH')}}we-templates/{{ $media_file->video_file}}" target="_blank">
                                                        <i class="fa fa-download" aria-hidden="true"></i> View &amp; Download
                                                    </a>
                                                    @else
                                                    <a href="{{env('S3_PATH')}}we-templates/{{ $media_file->media_file}}" target="_blank">
                                                        <i class="fa fa-download" aria-hidden="true"></i> View &amp; Download
                                                    </a>
                                                @endif  
                                                </p>
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endforeach
                                @endif

                                {{--<div class="row mb-3">
                                    @if(isset($media_files_group['Branded']))
                                        @foreach($media_files_group['Branded'] as $media_file)
                                        <div class="col-md-3">
                                            <a href="{{ route('marketing-category-detail', ['category_name' => $record->slug])}}" class="text-center">
                                                <img src="/images/{{$record->media_name}}" alt="">
                                                <p>{{$record->category_name}}</p>
                                            </a>
                                        </div>
                                        @endforeach
                                    @endif
                                </div>--}}

                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-12">
                                    <a href="{{route('marketing-collateral')}}" class="btn btn-secondary btn-sm">Back</a>
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
