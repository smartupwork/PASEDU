{{--<h3>@php dump($data) @endphp</h3>--}}
<div class="row">
    {{--<div class="col-md-6">
        <div class="text-center">
            <p>Old Logo</p>
            <img id="images" style="max-width: 200px;" src="{{env('S3_PATH')}}partner/{{ $data['old_data']['logo']}}" class="img-fluid" alt="">
        </div>
    </div>--}}
    <div class="col-md-6">
        <div class="text-center">
            <p>New Logo</p>
            <img id="images" style="max-width: 200px;" src="{{env('S3_PATH')}}partner/{{ $data['new_data']['logo']}}" class="img-fluid" alt="">
        </div>
    </div>
</div>