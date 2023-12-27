@extends('layout.main')
@section('content')

    <div class="section__content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">{{$parent_category->category_name}}</div>
                        <div class="card-body card-block m-3 psp-temp">
                            @if(\App\Models\UserAccess::hasAccess(\App\Models\UserAccess::WE_TEMPLATE_ACCESS, 'add'))
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
                                                @if(\App\Models\UserAccess::hasAccess(\App\Models\UserAccess::WE_TEMPLATE_ACCESS, 'download'))
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
                                                    @if(\App\Models\UserAccess::hasAccess(\App\Models\UserAccess::WE_TEMPLATE_ACCESS, 'download'))
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
                                    <a href="{{route('course-marketing')}}" class="btn btn-secondary btn-sm">Back to Course Marketing</a>
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
