<form name="banner_form" id="banner_form" action="{{route('prestashop-banner-save')}}" method="post" enctype="multipart/form-data">
    {{csrf_field()}}
    <input type="hidden" name="id" id="banner_id" value="{{ isset($edit_record) ? $edit_record->id:''}}">
    <input type="hidden" name="old_media_file" id="old_media_file" value="{{ isset($edit_record) ? $edit_record->media_file:''}}">
    <div class="col-md-6">

        <div class="text-center">
            @if(isset($edit_record) && $edit_record->media_file != '')
                <img id="images" style="max-width: 200px;" src="{{env('S3_PATH')}}ps-banner/{{$edit_record->media_file}}" alt=""/>
            @else
                <img id="images" src="{{$CDN_URL}}dashboard/images/profile-picture.png" alt=""/>
            @endif
        </div>

        <div class="custom-file mb-3">
            <input type="file" class="custom-file-input" id="media_file" name="banner[media_file]" onchange="preview();">
            <label class="custom-file-label" for="media_file">Choose Banner <span class="text-danger">*</span></label>
        </div>

        <div class="form-group">
            <label for="title" class="form-control-label">Title </label>
            <input type="text" maxlength="100" id="title" name="banner[title]" value="{{ isset($edit_record) ? $edit_record->title:''}}" class="form-control">
        </div>

        <div class="form-group">
            <label for="link" class="form-control-label">Link </label>
            <input type="text" maxlength="1000" id="link" name="banner[link]" value="{{ isset($edit_record) ? $edit_record->link:''}}" class="form-control">
        </div>

        <div class="form-group">
            <label for="description" class="form-control-label">Description</label>
            <textarea rows="2" id="description" maxlength="100" name="banner[description]" class="form-control">{{ isset($edit_record) ? $edit_record->description:''}}</textarea>
        </div>

        <div class="form-group">
            <label class="open_new_tab">Open in new tab</label>
            <div class="form-check">
                <input type="checkbox" id="open_new_tab" name="banner[open_new_tab]" value="1" @if(isset($edit_record) && $edit_record->open_new_tab == 1 ) checked @endif>
                <label for="description" class="form-control-label">Yes</label>
            </div>
        </div>

        <div class="form-group">
            <label class="status">Status</label>
            <div class="form-check">

                <div class="pl-4" id="schedule_interval_container" style="">
                    <p>
                        <label class="week">
                            <input type="radio" name="banner[is_active]" class="form-check-input" value="1" @if(isset($edit_record) && $edit_record->is_active == 1 ) checked @elseif(!isset($edit_record)) checked @endif>Active
                        </label>
                    </p>
                    <p>
                        <label class="one-month">
                            <input type="radio" name="banner[is_active]" class="form-check-input" value="0" @if(isset($edit_record) && $edit_record->is_active == 0 ) checked @endif>Deactivate
                        </label>
                    </p>
                </div>
            </div>
        </div>

    </div>

    <div class="row">
        <div class="col-md-12">
            <button type="reset" class="btn btn-secondary btn-sm float-right ml-1"> Reset</button>
            <button type="submit" class="btn btn-primary btn-sm  float-right" id="submit-btn">Submit</button>
        </div>
    </div>

</form>
@section('myJsFile')
    <script src="{{$CDN_URL}}js/jquery.form.js"></script>
<script>
    function preview(){
        $('#images').attr('src', URL.createObjectURL(event.target.files[0]));
    }

    $('#banner_form').ajaxForm({
        beforeSubmit: function() {
            $("#submit-btn").html('Processing...');
        },
        dataType: 'json',
        success: function(data) {
            $("#submit-btn").html('Save');
            if(data.status == "success"){
                alert(data.message);
                window.location.reload();
            }else{
                alert(data.message);
            }
        },
        error: function(xhr){
            if(xhr.status === 419){
                window.location.reload();
            }else{
                $("#submit-btn").html('Save');
                alert(xhr.responseJSON.message);
            }
        }
    });
</script>
@stop