@extends('layout.main')
@section('content')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="/colour-picker/spectrum.css">
    <style>
        .error-message{
            color: red;
            font-size: 14px;
        }
    </style>
    <div class="section__content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            Shop Creator
                        </div>
                        <div class="card-body card-block m-3 psp-temp">
                            <form name="shop_creator_form" id="shop_creator_form" method="post" enctype="multipart/form-data">
                                {{csrf_field()}}
                                {{--<input type="hidden" name="id" id="contact_setting_id" value="{{ isset($edit_record) ? $edit_record->id:''}}">--}}
                                {{--<input type="hidden" name="contact[type]" id="type" value="refer-friend">--}}
                                {{--<input type="hidden" name="old_image" id="old_image" value="{{ isset($contact_detail['image']) ? $contact_detail['image']:''}}">--}}
                                <input type="hidden" name="shop[id_shop_group]" id="partner_type">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <div>
                                                    <label for="street" class="form-control-label">Zoho Partner/Affiliate <span class="text-danger">*</span></label>
                                                    <select name="zoho[partner]" id="partner" class="form-control select2-drop">
                                                        <option value="">Select Partner/Affiliate </option>
                                                        @foreach($partner_affiliates as $partner_type => $partner_affiliate)
                                                            <optgroup label="{{$partner_type}}"></optgroup>
                                                            @foreach ($partner_affiliate as $partner)
                                                                <option value="{{$partner->id }}" data-shop="{{$partner->name}}" data-filters="{{$partner_type}}" data-email="{{$partner->email }}" data-phone="{{$partner->phone }}" data-street="{{ stripslashes($partner->street)  }}" data-city="{{$partner->city }}" data-state="{{$partner->state }}" data-zip="{{$partner->zip_code }}">{{ stripcslashes($partner->name) }} ({{$partner->zoho_id}})</option>
                                                            @endforeach
                                                        @endforeach
                                                    </select>
                                                </div>

                                            </div>
                                            <h3>Shop Info</h3>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <div>
                                                <label for="city" class="form-control-label">Shop Name <span class="text-danger">*</span></label>
                                                <input type="text" maxlength="150" id="shop_name" name="shop[name]" value="" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="shop_url" class="form-control-label">Shop Domain <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1">https://</span>
                                                </div>
                                                <input type="text" maxlength="150" id="shop_url" name="shop[url]" value="" class="form-control">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">.{{ env('CPANEL_ROOT_DOMAIN') }}</span>
                                                </div>
                                            </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <h3>Shop Optional Menu <span class="text-danger">*</span></h3>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <input type="checkbox" name="menu[]" value="Military" /> Military &nbsp;&nbsp;
                                                <input type="checkbox" name="menu[]" value="Promotions" /> Promotions&nbsp;&nbsp;
                                                <input type="checkbox" name="menu[]" value="Vocational Rehab" /> Vocational Rehab&nbsp;&nbsp;
                                                <input type="checkbox" name="menu[]" value="Workforce" /> Workforce&nbsp;&nbsp;
                                                <input type="checkbox" name="menu[]" value="MyCAA" /> MyCAA
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <div>
                                                <label for="street" class="form-control-label">Shop Logo <span class="text-danger">*</span></label>
                                                <input type="file" name="logo" class="form-control" />
                                                </div>
                                            </div>
                                            <h3>Shop Contact Info</h3>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="city" class="form-control-label">Email</label>
                                                <input type="text" maxlength="150" id="email" name="contact[email]" value="" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="zip_code" class="form-control-label">Address</label>
                                                <input type="text" maxlength="150" id="address" name="contact[address]" value="" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="zip_code" class="form-control-label">Phone</label>
                                                <input type="text" maxlength="150" id="phone" name="contact[phone]" value="" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="zip_code" class="form-control-label">City</label>
                                                <input type="text" maxlength="150" id="city" name="contact[city]" value="" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="zip_code" class="form-control-label">State</label>
                                                <input type="text" maxlength="150" id="state" name="contact[state]" value="" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="zip_code" class="form-control-label">Zip</label>
                                                <input type="text" maxlength="150" id="zip_code" name="contact[zip_code]" value="" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <h3>Shop Theme Colors <small style="font-size: 10px;">(Cannot be changed for Affiliates)</small></h3>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="zip_code" class="form-control-label">Header</label>
                                                <input type="text" name="shop[header]" class="form-control form-control-color color-picker" id="header" value="#093146" title="Choose your color">
                                                {{--<input type="text" maxlength="150" id="header" name="shop[header]" value="#093146" class="form-control">--}}
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="zip_code" class="form-control-label">Buttons</label>
                                                <input type="text" maxlength="150" id="button" name="shop[button]" value="#f15800" class="form-control color-picker">
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
                                <script src="/js/jquery.validate.min.js"></script>
                                <script src="/js/additional-methods.js"></script>
                                <script src="{{$CDN_URL}}js/jquery.form.js"></script>
                                <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script>
                                <script src="/colour-picker/spectrum.js"></script>
                            <script>
                                var textPalette = ["rgb(255, 255, 255)", "rgb(204, 204, 204)", "rgb(192, 192, 192)", "rgb(153, 153, 153)", "rgb(102, 102, 102)", "rgb(51, 51, 51)", "rgb(0, 0, 0)", "rgb(255, 204, 204)", "rgb(255, 102, 102)", "rgb(255, 0, 0)", "rgb(204, 0, 0)", "rgb(153, 0, 0)", "rgb(102, 0, 0)", "rgb(51, 0, 0)", "rgb(255, 204, 153)", "rgb(255, 153, 102)", "rgb(255, 153, 0)", "rgb(255, 102, 0)", "rgb(204, 102, 0)", "rgb(153, 51, 0)", "rgb(102, 51, 0)", "rgb(255, 255, 153)", "rgb(255, 255, 102)", "rgb(255, 204, 102)", "rgb(255, 204, 51)", "rgb(204, 153, 51)", "rgb(153, 102, 51)", "rgb(102, 51, 51)", "rgb(255, 255, 204)", "rgb(255, 255, 51)", "rgb(255, 255, 0)", "rgb(255, 204, 0)", "rgb(153, 153, 0)", "rgb(102, 102, 0)", "rgb(51, 51, 0)", "rgb(153, 255, 153)", "rgb(102, 255, 153)", "rgb(51, 255, 51)", "rgb(51, 204, 0)", "rgb(0, 153, 0)", "rgb(0, 102, 0)", "rgb(0, 51, 0)", "rgb(153, 255, 255)", "rgb(51, 255, 255)", "rgb(102, 204, 204)", "rgb(0, 204, 204)", "rgb(51, 153, 153)", "rgb(51, 102, 102)", "rgb(0, 51, 51)", "rgb(204, 255, 255)", "rgb(102, 255, 255)", "rgb(51, 204, 255)", "rgb(51, 102, 255)", "rgb(51, 51, 255)", "rgb(0, 0, 153)", "rgb(0, 0, 102)", "rgb(204, 204, 255)", "rgb(153, 153, 255)", "rgb(102, 102, 204)", "rgb(102, 51, 255)", "rgb(102, 0, 204)", "rgb(51, 51, 153)", "rgb(51, 0, 153)", "rgb(255, 204, 255)", "rgb(255, 153, 255)", "rgb(204, 102, 204)", "rgb(204, 51, 204)", "rgb(153, 51, 153)", "rgb(102, 51, 102)", "rgb(51, 0, 51)"];

                                $(".color-picker").spectrum({
                                    //flat: true,
                                    preferredFormat: "hex",
                                    showInput: true,
                                    showSelectionPalette: true,
                                    showPalette: true,
                                    palette: textPalette
                                });

                                $("#shop_creator_form").validate({ // initialize the plugin
                                    // any other options,
                                    onkeyup: false,
                                    rules: {
                                        'zoho[partner]': {
                                            required: true,
                                        },
                                        'shop[name]': {
                                            required: true,
                                            minlength: 3,
                                            async: false,
                                            remote: {
                                                url: "{{route('check-shop-name')}}",
                                                type: "POST",
                                                data: {
                                                    _token: '{{csrf_token()}}',
                                                    field: 'name',
                                                    name: function () {
                                                        return $('#shop_name').val()
                                                    },
                                                }
                                            }
                                        },
                                        'shop[url]': {
                                            required: true,
                                            minlength: 2,
                                            async: false,
                                            remote: {
                                                url: "{{route('check-shop-url')}}",
                                                type: "POST",
                                                data: {
                                                    _token: '{{csrf_token()}}',
                                                    field: 'domain',
                                                    url: function () {
                                                        return $('#shop_url').val()
                                                    },
                                                }
                                            },
                                        },
                                        'logo': {
                                            required: true,
                                        },
                                        /*'contact[address]': {
                                            minlength: 2,
                                            maxlength: 500
                                        },*/
                                    },
                                    messages: {
                                        'contact[email]': {
                                            required: 'Please enter email address.',
                                            'user[email]': 'Please enter valid email address.',
                                        }
                                    },
                                    errorElement: "div",
                                    errorClass: "error-message",
                                    //onfocusout: true,
                                    onclick: false,
                                    onfocusin: false,
                                    errorPlacement: function (error, element) {
                                        element.parent().after(error);
                                    },
                                    highlight: function (element) {
                                        jQuery(element).parent().addClass("has-error");
                                    },
                                    unhighlight: function (element) {
                                        jQuery(element).parent().removeClass("has-error");
                                    },
                                    invalidHandler: function (form, validator) {
                                        if (!validator.numberOfInvalids())
                                            return;
                                        $('html, body').animate({
                                            scrollTop: $(validator.errorList[0].element).offset().top - 150
                                        }, "fast");
                                    },
                                });

                                $('#shop_creator_form').ajaxForm({
                                    beforeSubmit: function() {
                                        $("#submit-btn").html('Processing...');
                                    },
                                    dataType: 'json',
                                    success: function(data) {
                                        $("#submit-btn").html('Submit');
                                        if(data.shop_url != ''){
                                            $("#shop_url_href").attr('href', data.shop_url);
                                            $('#shop_response').modal('show');
                                        }else{
                                            alert(data.message);
                                            window.location.reload();
                                        }
                                    },
                                    error: function(xhr){
                                        if(xhr.status === 419){
                                            window.location.reload();
                                        }else{
                                            $("#submit-btn").html('Submit');
                                            alert(xhr.responseJSON.message);
                                        }
                                    }
                                });

                                $(document).on('change', '#partner', function(){
                                    $('#shop_name').val($(this).children("option:selected").data('shop'));
                                    $('#partner_type').val($(this).children("option:selected").data('filters'));
                                    $('#email').val($(this).children("option:selected").data('email'));
                                    $('#phone').val($(this).children("option:selected").data('phone'));
                                    $('#address').val($(this).children("option:selected").data('street'));
                                    $('#city').val($(this).children("option:selected").data('city'));
                                    $('#state').val($(this).children("option:selected").data('state'));
                                    $('#zip_code').val($(this).children("option:selected").data('zip'));
                                    $('#shop_url').val(convertToSlug($('#shop_name').val()));
                                });

                                $(document).on('change, keyup, keydown', '#shop_name', function(){
                                    $('#shop_url').val(convertToSlug($(this).val()))
                                });

                                function convertToSlug(Text) {
                                    return Text.toLowerCase()
                                        .replace(/ /g, '-')
                                        .replace(/[^\w-]+/g, '');
                                }

                            </script>
                            @stop
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="shop_response" tabindex="-1" role="dialog" aria-labelledby="infomodalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infomodalLabel">Shop Created</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>
                        Shop URL: <a href="#" id="shop_url_href" target="_blank">Click to open</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

@stop
