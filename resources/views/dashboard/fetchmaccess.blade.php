<div class="row" id="fullaccess">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header"> Features and Access</div>
            <div class="card-body card-block m-3 featuresaccess">
                @foreach($all_user_features as $feature_key => $access)
                    <div class="row mt-2" id="{{$feature_key}}">
                        <div class="col-md-8">{{$access['label']}}</div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input name="feature[{{$feature_key}}][feature]" value="{{$feature_key}}" type="checkbox" class="form-check-input" {{$access['is_checked'] ? 'checked':''}}>
                                <input name="feature[{{$feature_key}}][parent_menu]" type="hidden" value="{{$access['parent_menu']}}">
                                <label class="form-check-label" for="fview1">Yes</label>
                            </div>
                        </div>
                    </div>
                    @if(isset($access['options']))
                        @foreach($access['options'] as $option_key => $option)

                            <div class="row {{$option['id']}}">
                                <div class="col-md-8">
                                    <div class="ml-4">
                                        {{--@if($option_key != 'view')--}}
                                        {{$option['label']}}
                                        {{--@endif--}}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check-inline form-check">
                                        @if($option_key != 'view')
                                            <label for="{{$option['id']}}" class="form-check-label ">
                                                <input name="feature[{{$feature_key}}][opt][{{$option_key}}]" type="radio" value="1" class="form-check-input" {{$option['is_checked'] ? 'checked="checked"':''}}>Yes
                                            </label>

                                            <label for="{{$option['id']}}" class="form-check-label ">
                                                <input name="feature[{{$feature_key}}][opt][{{$option_key}}]" type="radio" value="0" class="form-check-input" {{!$option['is_checked'] ? 'checked="checked"':''}}>No
                                            </label>
                                        @else
                                            <label for="{{$option['id']}}" class="form-check-label ">
                                                <input name="feature[{{$feature_key}}][opt][{{$option_key}}]" type="checkbox" value="1" class="form-check-input" {{$option['is_checked'] ? 'checked="checked"':''}}>Yes
                                            </label>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                @endforeach

            </div>
        </div>
    </div>
</div>

<div class="card-footer">
    <div class="row">
        <div class="col-md-12">
            <a href="{{route('my-users')}}" class="btn btn-secondary btn-sm">Back to Users List</a>
            <button type="submit" class="btn btn-primary btn-sm  float-right">Save </button>
        </div>
    </div>

</div>