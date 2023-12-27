<div class="table-responsive m-b-20">
    <table style="width:100%" class="table table-earning data-table">
        <tr style="font-size: 14px;">
            <th>S.No</th>
            <th>Partner Name</th>
            <th>Contact Name</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Street</th>
            <th>Zip Code</th>
            <th>Partner Type</th>
        </tr>
        @foreach($data['new_data'] as $index => $student)

            <tr>
                <td>{{ $index + 1 }}</td>
                <td style="text-align: left;">{{ $student['partner_name'] }}</td>
                <td style="text-align: left;">{{ $student['contact_name'] }}</td>
                <td style="text-align: left;">{{ $student['phone'] }}</td>
                <td style="text-align: left;">{{ $student['email'] }}</td>
                <td style="text-align: left;">{{ $student['street'] }}</td>
                <td style="text-align: left;">{{ $student['zip_code'] }}</td>
                <td style="text-align: left;">{{ $student['partner_type'] }}</td>
            </tr>
        @endforeach
    </table>
</div>
