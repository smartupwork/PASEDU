@extends('layout.main')
@section('content')

    <div class="section__content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">Course Marketing Material</div>
                        <div class="card-body card-block m-3 psp-temp">
                            <div class="mx-auto col-md-10">
                                <p class="mb-3">
                                Here, you will find access to some of our most relevant and recent program marketing collateral. Options have been developed to provide you with World Education branded and unbranded collateral, social media post copy, and social media pictures. Please take time to explore and provide feedback on your success!
                                </p>

                                <div class="row mb-3">
                                    @foreach($records as $record)
                                    <div class="col-md-3">
                                        <a href="{{ route('course-marketing-category', ['course_marketing' => $record->slug])}}" class="text-center">
                                            <img src="/images/{{$record->media_name}}" alt="">
                                            <p>{{$record->category_name}}</p>
                                        </a>
                                    </div>
                                    @endforeach
                                </div>

                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-12">
                                    <a href="{{route('we-templates')}}" class="btn btn-secondary btn-sm">Back</a>
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
                        $("#submit-btn").html('Save').attr('disabled', false);
                        alert(xhr.responseJSON.message);
                    }
                }
            });

        });

    </script>
@stop
@stop
