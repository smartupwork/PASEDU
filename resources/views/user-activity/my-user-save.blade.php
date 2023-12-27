@php
    $status = \App\Utility::getStatus();
        @endphp
<style>
    .data-table tbody tr td:last-child{ text-align:left; padding-right: 10px;}
    .data-table tbody td{ padding: 12px 10px 12px 5px;}
</style>
<div class="table-responsive m-b-20">
    <table style="widtd:100%" class="table table-earning data-table">
        @if(isset($data['new_data']['photo']))
        <tr>
            <td><strong>Photo</strong></td>
            <td><img id="images" style="max-width: 200px;" src="{{env('S3_PATH')}}partner/{{ $data['new_data']['photo']}}" class="img-fluid" alt=""></td>
        </tr>
        @endif
        <tr>
            <td><strong>First Name</strong></td>
            <td>{{ $data['new_data']['firstname'] }}</td>
        </tr>
        <tr>
            <td><strong>Last Name</strong></td>
            <td>{{ $data['new_data']['lastname'] }}</td>
        </tr>
        <tr>
            <td><strong>Role</strong></td>
            <td>{{ \Illuminate\Support\Facades\DB::table('pas_roles')->where('id', '=', $data['new_data']['roleid'])->value('role_name') }}</td>
        </tr>
        <tr>
            <td><strong>Email</strong></td>
            <td>{{ $data['new_data']['email'] }}</td>
        </tr>
        <tr>
            <td><strong>Phone</strong></td>
            <td>{{ $data['new_data']['phone'] }}</td>
        </tr>
        <tr>
            <td><strong>Status</strong></td>
            <td>{{ (isset($status[$data['new_data']['status']]) ? $status[$data['new_data']['status']]:'') }}</td>
        </tr>
    </table>
</div>
