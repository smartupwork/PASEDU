<div class="table-responsive m-b-20">
    <table style="width:100%" class="table table-earning data-table">
        <tr style="font-size: 14px;">
            <th>S.No</th>
            <th>Deal Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Street</th>
            <th>City</th>
            <th>State</th>
            <th>Zip Code</th>
            <th>Country</th>
            {{--<th>enrollment_created_at</th>
            <th>enrollment_updated_at</th>--}}
        </tr>
        @foreach($data['new_data'] as $index => $student)

            <tr>
                <td>{{ $index + 1 }}</td>
                <td style="text-align: left;">{{ $student['deal_name'] }}</td>
                <td style="text-align: left;">{{ $student['email'] }}</td>
                <td style="text-align: left;">{{ $student['phone'] }}</td>
                <td style="text-align: left;">{{ $student['street'] }}</td>
                <td style="text-align: left;">{{ $student['city'] }}</td>
                <td style="text-align: left;">{{ $student['state'] }}</td>
                <td style="text-align: left;">{{ $student['zip'] }}</td>
                <td style="text-align: left;">{{ $student['country'] }}</td>
                {{--<td style="text-align: left;">{{ $student['status'] }}</td>--}}
            </tr>
        @endforeach
    </table>
</div>
