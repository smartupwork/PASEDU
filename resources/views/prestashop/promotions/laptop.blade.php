@extends('layout.main')
@section('content')

    <div class="section__content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            Laptop Promotions
                            <span style="float: right;">
                                <a class="btn btn-primary" href="{{ route('refer-friend') }}">Refer a Friend</a>
                            </span>
                        </div>
                        <div class="card-body card-block m-3 psp-temp">
                            @include('prestashop.promotions._form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
