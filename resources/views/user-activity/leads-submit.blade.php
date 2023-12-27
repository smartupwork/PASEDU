<div class="table-responsive m-b-20">
    <table style="width:100%" class="table table-earning data-table">
        <tr style="font-size: 14px;">
            <th>S.No</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Street</th>
            <th>Country</th>
            <th>State</th>
            <th>City</th>
            <th>Zip Code</th>
            <th>Interested Program</th>
            <th>Financing Needs	</th>
            <th>Category Of Interest</th>
            <th>Time Zone</th>
        </tr>
        @foreach($data as $index => $student)

            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $student['First_Name'] }}</td>
                <td>{{ $student['Last_Name'] }}</td>
                <td>{{ $student['Email'] }}</td>
                <td>{{ $student['Phone'] }}</td>
                <td>{{ $student['Street'] }}</td>
                <td>{{ $student['Country'] }}</td>
                <td>{{ $student['State'] }}</td>
                <td>{{ $student['City'] }}</td>
                <td>{{ $student['Zip_Code'] }}</td>
                <td>{{ $student['Program']['name'] ?? '' }}</td>
                <td>{{ $student['Financing_Needs'] }}</td>
                <td>{{ $student['Category_of_Interest'] }}</td>
                <td>{{ $student['Time_Zone'] }}</td>
            </tr>
        @endforeach
    </table>
</div>
