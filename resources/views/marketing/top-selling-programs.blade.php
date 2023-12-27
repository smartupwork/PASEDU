@extends('layout.main')
@section('content')

    <div class="section__content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            My Top Selling Programs
                        </div>
                        <div class="card-body">
                            <div class="row m-t-25 pas-badges">
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <strong class="card-title mb-3">Top 5 Career Training Programs</strong>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-group top-sell">
                                                @if(count($top_selling_programs['Career Training Program']) > 0)
                                                    @foreach($top_selling_programs['Career Training Program'] as $program)
                                                        <li class="list-group-item">{{$program['program_name']}}</li>
                                                    @endforeach
                                                @else
                                                    <li class="list-group-item text-center text-danger">No Programs</li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <strong class="card-title mb-3">Top 5 Professional Enrichment</strong>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-group top-sell">
                                                @if(count($top_selling_programs['Professional Enrichment']) > 0)
                                                    @foreach($top_selling_programs['Professional Enrichment'] as $program)
                                                        <li class="list-group-item">{{$program['program_name']}}</li>
                                                    @endforeach
                                                @else
                                                    <li class="list-group-item text-center text-danger">No Programs</li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <strong class="card-title mb-3">Top CE</strong>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-group top-sell">
                                                @if(count($top_selling_programs['Continuing Education']) > 0)
                                                    @foreach($top_selling_programs['Continuing Education'] as $program)
                                                        <li class="list-group-item">{{$program['program_name']}}</li>
                                                    @endforeach
                                                @else
                                                    <li class="list-group-item text-center text-danger">No Programs</li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@stop

