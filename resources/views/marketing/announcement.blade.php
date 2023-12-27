@extends('layout.main')
@section('content')

<div class="section__content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        {{ ucwords($heading) }}
                    </div>
                    <div class="card-body mt-5">
                        <div class="col-md-8 mx-auto">

                            @if(count($results) > 0) @foreach($results as $key => $result)
                            <div class="form-group">
                                <button class="btn btn-default news-all" data-toggle="modal" data-target="#mediumModal-{{$key}}">
                                    <p>
                                        <b>{{$result->title}}</b>
                                    </p>
                                    <p> {{ date('h:i a M d', strtotime($result->updated_at)) }} </p>
                                </button>
                            </div>
                            @endforeach @else
                            <p>There is no {{$heading}}</p>
                            @endif

                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

@foreach($results as $key => $result)
<div class="modal fade" id="mediumModal-{{$key}}" tabindex="-1" role="dialog" aria-labelledby="mediumModalLabel-{{$key}}" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mediumModalLabel-{{$key}}"> {{ ucwords($heading) }} </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 2rem;">
                <h2>{{$result->title}}</h2>
                <p class="font-14">{{ date('M d, Y', strtotime($result->updated_at)) }} </p>
                {!! $result->description !!}
            </div>
        </div>
    </div>
</div>
@endforeach

@stop