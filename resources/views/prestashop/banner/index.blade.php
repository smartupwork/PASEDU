<?php
use \App\Models\UserAccess;

?>
@extends('layout.main')
@section('content')

    <div class="section__content">
        <div class="container-fluid">
            <h2 class="page-titel m-b-20">Banner</h2>

            <div class="row adv-search-row">
                <div class="col-md-5">

                </div>
                <div class="col-md-7 text-right pt-3 pb-2">

                    <a href="{{ route('prestashop-banner-create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-user-plus"></i> Banner Entry</a>
                </div>
            </div>

            {{--<form id="prestasho-menu-form" name="prestasho-menu-form" method="post" action="{{ route('prestashop-banner-save') }}">
                {{ csrf_field() }}--}}
                <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive m-b-20" id="listid">
                        <table class="table table-earning data-table dataTable no-footer" id="table_id" data-total="1" role="grid">
                            <thead>
                            <tr role="row">
                                {{--<th class="reorder" rowspan="1" colspan="1">
                                    <input type="checkbox" class="checkallboxlist">
                                </th>--}}
                                <th>Banner</th>
                                <th>Description</th>
                                <th>Open in new tab</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>

                            @if(count($banners) > 0)
                            @foreach($banners as $banner)
                            <tr>
                                {{--<td>
                                    <input type="checkbox" class="chklist" name="menu_name[]" value="Military" @if(in_array('Military', $menu_array)) checked @endif>
                                </td>--}}
                                <td>
                                    @if(!empty($banner->media_file))
                                        <img src="{{ env('S3_PATH').'ps-banner/'.$banner->media_file}}" width="300">
                                    @endif
                                </td>
                                <td>{{$banner->description}}</td>
                                <td>{{ $banner->open_new_tab == 1 ? 'Yes':'No' }}</td>
                                <td>{{ $banner->is_active == 1 ? 'Active':'Deactive' }}</td>
                                <td class=" reorder">
                                    <a title="Edit" href="{{ route('prestashop-banner-update', ['id' => pas_encrypt($banner->id)]) }}">
                                        <i class="fa fa-id-card" aria-hidden="true"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td colspan="5">No record found</td>
                            </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                    {{--<div class="row">
                        <div class="col-md-4">
                            <p class="text-left" id="total-records"></p>
                        </div>
                        <div class="col-md-8 text-right">
                            <button type="submit" class="btn btn-secondary btn-sm" id="submit-btn">
                                <i class="fa fa-save" aria-hidden="true"></i> Save
                            </button>
                        </div>
                    </div>--}}
                </div>
            </div>
            {{--</form>--}}
            @include('layout.dashboard.footer')
        </div>
    </div>

@section('myCssFiles')

@stop

@section('myJsFile')

    <script src="{{$CDN_URL}}js/jquery.form.js"></script>

    <script>

        $(document).on('click', '.checkallboxlist', function(){
            if($(this).is(':checked') === true){
                $('.chklist').prop('checked',true);
            }else{
                $('.chklist').prop('checked',false);
            }
        });

        $('#prestasho-menu-form').ajaxForm({
            beforeSubmit: function() {
                $("#submit-btn").html('Processing...').attr('disabled', true);
            },
            dataType: 'json',
            success: function(data) {
                alert(data.message);
                if(data.status == "success"){
                    window.location.reload();
                }
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


    </script>

    <script src="{{$CDN_URL}}js/jquery.form.js"></script>
    <script type="text/javascript">

    </script>
@stop
@stop