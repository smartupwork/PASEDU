<form name="contact_setting_form" id="contact_setting_form" action="" method="post" enctype="multipart/form-data">
    {{csrf_field()}}
    <input type="hidden" name="id" id="contact_setting_id" value="{{ isset($edit_record) ? $edit_record->id:''}}">
    <input type="hidden" name="contact[type]" id="type" value="laptop-content">
    <input type="hidden" name="old_image" id="old_image" value="{{ isset($contact_detail['image']) ? $contact_detail['image']:''}}">
    <div class="col-md-12">

        <div class="row">
            <div class="col-md-12">
                <div class="text-center">
                    @if(isset($contact_detail['image']) && $contact_detail['image'] != '')
                        <img id="images" style="max-width: 200px;" src="{{env('S3_PATH')}}ps-banner/{{$contact_detail['image']}}" alt=""/>
                    @else
                        <img id="images" src="{{$CDN_URL}}dashboard/images/profile-picture.png" alt=""/>
                    @endif
                </div>
                <div class="form-group">
                    <label for="street" class="form-control-label">Image </label>
                    <input type="file" id="image" name="contact[image]" accept="image/*" value="" class="form-control">
                </div>
                <div class="form-group">
                    <label for="city" class="form-control-label">Title </label>
                    <input type="text" maxlength="150" id="title" name="contact[title]" value="{{ isset($contact_detail['title']) ? $contact_detail['title']:''}}" class="form-control">
                </div>

                <div class="form-group">
                    <label for="zip_code" class="form-control-label">Detail</label>
                    <textarea name="contact[detail]" maxlength="500" class="form-control" id="detail" rows="5">{{ isset($contact_detail['detail']) ? $contact_detail['detail']:''}}</textarea>
                </div>

                <div class="form-group">
                    <label class="status">Display</label>
                    <div class="form-check">

                        <div class="pl-4" id="schedule_interval_container" style="">
                            <p>
                                <label class="week">
                                    <input type="radio" name="is_active" class="form-check-input" value="1" <?php if(isset($edit_record) && $edit_record->is_active == 1 ): ?> checked <?php elseif(!isset($edit_record)): ?> checked <?php endif; ?>>On
                                </label>
                            </p>
                            <p>
                                <label class="one-month">
                                    <input type="radio" name="is_active" class="form-check-input" value="0" <?php if(isset($edit_record) && $edit_record->is_active == 0 ): ?> checked <?php endif; ?>>Off
                                </label>
                            </p>
                        </div>
                    </div>
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

    $('#contact_setting_form').ajaxForm({
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