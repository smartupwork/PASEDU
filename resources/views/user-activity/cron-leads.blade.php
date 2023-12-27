<div class="table-responsive m-b-20">
    <table style="width:100%" class="table table-earning data-table">
        <tr style="font-size: 14px;">
            <th>S.No</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Financing Needs</th>
            <th>Category Of Interest</th>
            <th>Inquiry Message</th>
        </tr>
        @foreach($data['new_data'] as $index => $student)

            <tr>
                <td>{{ $index + 1 }}</td>
                <td style="text-align: left;">{{ $student['firstname'] }}</td>
                <td style="text-align: left;">{{ $student['lastname'] }}</td>
                <td style="text-align: left;">{{ $student['email'] }}</td>
                <td style="text-align: left;">{{ $student['phone'] }}</td>
                <td style="text-align: left;">{{ $student['address'] }}</td>
                <td style="text-align: left;">{{ $student['financing_needs'] }}</td>
                <td style="text-align: left;">{{ $student['category_of_interest'] }}</td>
                <td style="text-align: left;">{{ $student['inquiry_message'] }}</td>
            </tr>
        @endforeach
    </table>
</div>
