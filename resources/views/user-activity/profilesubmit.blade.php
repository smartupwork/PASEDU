{{--<h3>@php dump($data) @endphp</h3>--}}
<div class="row">

    {{--<div class="col-md-6">
        <div class="row">
            <div class="col-md-4">
                    <strong>Old Logo</strong>
            </div>
            <div class="col-md-8">
                <img id="images" style="max-width: 200px;" src="{{env('S3_PATH')}}partner/{{ $data['old_data']['photo']}}" class="img-fluid" alt="">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                    <strong>Old Phone</strong>
                </div>
                <div class="col-md-8">
                    {{ $data['old_data']['phone']}}
                </div>
        </div>
    </div>--}}

    <div class="col-md-6">
        <div class="row">
            <div class="col-md-4">
                <strong>New Logo</strong>
            </div>
            <div class="col-md-8">
                <img id="images" style="max-width: 200px;" src="{{env('S3_PATH')}}partner/{{ $data['new_data']['photo']}}" class="img-fluid" alt="">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <strong>New Phone</strong>
            </div>
            <div class="col-md-8">
                {{ isset($data['new_data']['phone']) ? $data['new_data']['phone']:''}}
            </div>
        </div>
    </div>

</div>