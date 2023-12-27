@extends('layout.main')
@section('content')

    <div class="section__content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">Banner Notification</div>
                        <div class="card-body card-block m-3 psp-temp">
                            @include('prestashop.banner-notification._form')
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-12">
                                    <a href="{{route('prestashop-banner-notification')}}" class="btn btn-secondary btn-sm">Back to Banner</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
