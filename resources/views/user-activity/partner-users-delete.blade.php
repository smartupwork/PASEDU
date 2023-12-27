@php
    $status = \App\Utility::getStatus();
        @endphp

<div class="table-responsive m-b-20">
    <table style="widtd:100%" class="table table-earning data-table">
        <tr>
            <td><strong>S. No.</strong></td>
            <td><strong>Photo</strong></td>
            <td><strong>First Name</strong></td>
            <td><strong>Last Name</strong></td>
            <td><strong>Role</strong></td>
            <td><strong>Email</strong></td>
            <td><strong>Phone</strong></td>
            <td><strong>Status</strong></td>
            <td><strong>Partner/Institution</strong></td>
            <td><strong>Partner Type</strong></td>
        </tr>
        @foreach($data['old_data'] as $index => $data)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>
                @if(isset($data['photo']))
                <img id="images" style="max-width: 200px;" src="{{env('S3_PATH')}}partner/{{ $data['photo']}}" class="img-fluid" alt="">
                @endif
            </td>
            <td>{{ $data['firstname'] }}</td>
            <td>{{ $data['lastname'] }}</td>
            <td>{{ $data['role_name'] }}</td>
            <td>{{ $data['email'] }}</td>
            <td>{{ $data['phone'] }}</td>
            <td>{{ isset($status[$data['status']]) ? $status[$data['status']]:'' }}</td>
            <td>{{ $data['partner_name'] }}</td>
            <td>{{ $data['partner_type'] }}</td>
        </tr>
        @endforeach
    </table>
</div>
