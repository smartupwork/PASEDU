<?php
use \App\Models\UserAccess;

?>
@extends('layout.main')
@section('content')

    <div class="section__content">
        <div class="container-fluid">
            <h2 class="page-titel m-b-20">Optional Menu tabs Configuration</h2>
            <form id="prestasho-menu-form" name="prestasho-menu-form" method="post" action="{{ route('prestashop-menu-update') }}">
                {{ csrf_field() }}
                <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive m-b-20" id="listid">
                        <table class="table table-earning data-table dataTable no-footer" id="table_id" data-total="1" role="grid">
                            <thead>
                            <tr role="row"><th id="checkbox_sort" class="reorder sorting_asc" rowspan="1" colspan="1" aria-label="

        " style="width: 45px;">
                                    <input type="checkbox" class="checkallboxlist">
                                </th>
                                <th style="width: 100px; text-align: left;">Menu</th>
                            </tr>
                            </thead>
                            <tbody>

                            <tr>
                                <td>
                                    <input type="checkbox" class="chklist" name="menu_name[]" value="Military" @if(in_array('Military', $menu_array)) checked @endif>
                                </td>
                                <td style="width: 100px; text-align: left;">Military</td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="checkbox" class="chklist" name="menu_name[]" value="Promotions" @if(in_array('Promotions', $menu_array)) checked @endif>
                                </td>
                                <td style="width: 100px; text-align: left;">Promotions</td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="checkbox" class="chklist" name="menu_name[]" value="Vocational Rehab" @if(in_array('Vocational Rehab', $menu_array)) checked @endif>
                                </td>
                                <td style="width: 100px; text-align: left;">Vocational Rehab</td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="checkbox" class="chklist" name="menu_name[]" value="Workforce" @if(in_array('Workforce', $menu_array)) checked @endif>
                                </td>
                                <td style="width: 100px; text-align: left;">Workforce</td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="checkbox" class="chklist" name="menu_name[]" value="MyCAA" @if(in_array('MyCAA', $menu_array)) checked @endif>
                                </td>
                                <td style="width: 100px; text-align: left;">MyCAA</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="text-left" id="total-records"></p>
                        </div>
                        <div class="col-md-8 text-right">
                            <button type="submit" class="btn btn-secondary btn-sm" id="submit-btn">
                                <i class="fa fa-save" aria-hidden="true"></i> Save
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            </form>
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