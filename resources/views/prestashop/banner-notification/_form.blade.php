<form name="banner_notification_form" id="banner_notification_form" action="{{route('prestashop-banner-notification-save')}}" method="post" enctype="multipart/form-data">
    {{csrf_field()}}
    <input type="hidden" name="id" id="banner_notification_id" value="{{ isset($edit_record) ? $edit_record->id:''}}">
    <input type="hidden" name="banner_notification[type]" id="is_active" value="banner-notification">
    <div class="col-md-12">

        <div class="form-group">
            <label for="content" class="form-control-label">Content</label>
            <textarea rows="2" id="content" maxlength="100" name="banner_notification[content]" id="content" class="form-control ckeditor">{{ isset($edit_record) ? $edit_record->content:''}}</textarea>
        </div>

        <div class="form-group">
            <label class="status">Status</label>
            <div class="form-check">

                <div class="pl-4" id="schedule_interval_container" style="">
                    <p>
                        <label class="week">
                            <input type="radio" name="banner_notification[is_active]" class="form-check-input" value="1" @if(isset($edit_record) && $edit_record->is_active == 1 ) checked @elseif(!isset($edit_record)) checked @endif>Active
                        </label>
                    </p>
                    <p>
                        <label class="one-month">
                            <input type="radio" name="banner_notification[is_active]" class="form-check-input" value="0" @if(isset($edit_record) && $edit_record->is_active == 0 ) checked @endif>Deactivate
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
    <script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
<script>
    function preview(){
        $('#images').attr('src', URL.createObjectURL(event.target.files[0]));
    }

    $('#banner_notification_form').ajaxForm({
        beforeSerialize:function($Form, options){
            /* Before serialize */
            for ( instance in CKEDITOR.instances ) {
                CKEDITOR.instances[instance].updateElement();
            }
            return true;
        },
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