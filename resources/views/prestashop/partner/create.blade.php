@extends('layout.main')
@section('content')

    <div class="section__content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">Hosted Site</div>
                        <div class="card-body card-block m-3 psp-temp">
                            @include('prestashop.partner._form')
                        </div>
                        {{--<div class="card-footer">
                            <div class="row">
                                <div class="col-md-12">
                                    <a href="{{route('prestashop-hosted-site')}}" class="btn btn-secondary btn-sm">Back to Banner</a>
                                </div>
                            </div>
                        </div>--}}
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
