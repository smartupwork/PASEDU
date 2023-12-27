@extends('layout.main')
@section('content')

    <div class="section__content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">Marketing Collateral</div>
                        <div class="card-body card-block m-3 psp-temp">
                            <h2 class="text-center mb-4">Marketing Resources</h2>
                            <div class="mx-auto col-md-10">
                                <p class="mb-3">
                                Welcome to our marketing corner! Explore our ever-expanding library of digital marketing resources, videos, guides, tools, and inspiration. Please take time to review our Course Marketing, Funding Sources, and Social Media marketing areas for e-collateral. Enjoy!
                                </p>
                                <div class="row">
                                    <div class="col-md-4">
                                        <a href="{{route('marketing-collateral-marketing-category', ['course_marketing' => 'course-marketing'])}}" class="text-center">
                                            <img src="/images/mr-1.jpeg" alt="">
                                            <p>Course Marketing</p>
                                        </a>
                                    </div>
                                    <div class="col-md-4">
                                        <a href="{{route('marketing-collateral-funding-sources')}}" class="text-center">
                                            <img src="/images/mr-2.jpeg" alt="">
                                            <p>Funding Sources</p>
                                        </a>
                                    </div>
                                    <div class="col-md-4">
                                        <a href="{{route('marketing-collateral-social-media')}}" class="text-center">
                                            <img src="/images/mr-3.jpeg" alt="">
                                            <p>Social Media</p>
                                        </a>
                                    </div>
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

            $('#frmid').ajaxForm({
                beforeSubmit: function() {
                    $("#submit-btn").html('Processing...').attr('disabled', true);
                    var cnt = $("input[name^='report']:checked").length;
                    if(cnt != 4){
                        document.getElementById("alert-error").innerHTML = 'You can select exactly 4 items!!';
                        $("#alert-error").show();
                        $("#submit-btn").html('Save').attr('disabled', false);
                        return false;
                    }
                },
                //dataType: 'json',
                success: function(data) {
                    $("#submit-btn").html('Save').attr('disabled', false);
                    $('input, select').removeClass('is-invalid');
                    if(data.status == "success"){
                        alert(data.msg);
                        window.location.reload();
                    }else{
                        alert(data.msg);
                    }
                },
                complete: function(){
                    $("#submit-btn").html('Save').attr('disabled', false);
                    //$('input, select').removeClass('is-invalid');
                },
                error: function(xhr){
                    if(xhr.status === 419){
                        window.location.reload();
                    }else{
                        alert(xhr.responseJSON.message);
                        window.location.reload();
                    }
                }
            });

        });

    </script>
@stop
@stop
