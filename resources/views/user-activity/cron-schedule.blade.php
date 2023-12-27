<div class="table-responsive m-b-20">
    <table style="width:100%" class="table table-earning data-table">
        <tr style="font-size: 14px;">
            <th>S.No</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Phone</th>
        </tr>
        @foreach($data['new_data'] as $index => $student)

            <tr>
                <td>{{ $index + 1 }}</td>
                <td style="text-align: left;">{{ $student['first_name'] }}</td>
                <td style="text-align: left;">{{ $student['last_name'] }}</td>
                <td style="text-align: left;">{{ $student['email'] }}</td>
                <td style="text-align: left;">{{ $student['phone'] }}</td>
            </tr>
        @endforeach
    </table>
</div>
