<?php
use \App\Models\UserAccess;
?>

@extends('layout.main')
@section('content')

    <div class="section__content">
        <div class="container-fluid">
            <h2 class="page-titel m-b-20">Student Warning Record</h2>

            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive m-b-20">
                        <table class="table table-earning data-table">
                            <thead>
                            <tr>
                                <th></th>
                                <th><strong>First Name</strong></th>
                                <th><strong>Last Name</strong></th>
                                <th><strong>Email</strong></th>
                                <th><strong>Program</strong></th>
                                <th><strong>Price Paid</strong></th>
                                <th><strong>Payment Type</strong></th>
                                <th><strong>Start Date</strong></th>
                                <th>End Date</th>
                                <th>Phone</th>
                                <th>Street</th>
                                <th>City</th>
                                <th><strong>State</strong></th>
                                <th>Zip</th>
                                <th><strong>Country</strong></th>
                            </tr>
                            </thead>
                            <tbody id="listid">
                            @if($skipped_records)
                                @foreach($skipped_records as $skipped_record)

                                    @php
                                        $error_html = '';
                                        if($skipped_record['errors']){ foreach ($skipped_record['errors'] as $column => $error) {
                                                $error_html .= '<strong>'.$column.':</strong> '.$error.'<br>';
                                            }
                                        }
                                    @endphp
                                        <tr>
                                            <td>
                                                <i class="fa fa-info-circle" data-toggle="popover" title="Skipped Record Detail" data-content="{{ $error_html }}"></i>

                                            </td>
                                            <td>{{$skipped_record['A']}}</td>
                                            <td>{{$skipped_record['B']}}</td>
                                            <td>{{$skipped_record['C']}}</td>
                                            <td>{{$skipped_record['D']}}</td>
                                            <td>{{$skipped_record['E']}}</td>
                                            <td>{{$skipped_record['F']}}</td>
                                            <td>{{$skipped_record['G']}}</td>
                                            <td>{{$skipped_record['H']}}</td>
                                            <td>{{$skipped_record['I']}}</td>
                                            <td>{{$skipped_record['J']}}</td>
                                            <td>{{$skipped_record['K']}}</td>
                                            <td>{{$skipped_record['L']}}</td>
                                            <td>{{$skipped_record['M']}}</td>
                                            <td>{{$skipped_record['N']}}</td>
                                        </tr>
                                @endforeach
                            @else
                                <tr><td colspan="15" style="text-align:center;">No Record Found.</td></tr>
                            @endif
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <a href="{{route('import-audit')}}" class="btn btn-secondary btn-sm">Back to Student Import Audit</a>
                </div>
            </div>
            @include('layout.dashboard.footer')
        </div>
    </div>

@section('myCssFiles')

@stop

    @section('myJsFile')
<script>
    $(function () {
        $('[data-toggle="popover"]').popover({html:true});

        $('body').on('click', function (e) {
            $('[data-toggle=popover]').each(function () {
                // hide any open popovers when the anywhere else in the body is clicked
                if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                    $(this).popover('hide');
                }
            });
        });
    })
</script>
    @stop
@stop

