<div class="table-responsive m-b-20">
    <table style="width:100%" class="table table-earning data-table">
        <tr style="font-size: 14px;">
            <th>S.No</th>
            <th>Name</th>
            <th>category</th>
            <th>Program Type</th>
            <th>Code</th>
            <th>Hours</th>
            <th>Retail Wholesale</th>
            <th>Description</th>
            <th>Displayed On</th>
            <th>Unite Price</th>
            <th>Certification Included</th>
            <th>Status</th>
        </tr>
        @foreach($data['new_data'] as $index => $student)

            <tr>
                <td>{{ $index + 1 }}</td>
                <td style="text-align: left;">{{ $student['name'] }}</td>
                <td style="text-align: left;">{{ $student['category'] }}</td>
                <td style="text-align: left;">{{ $student['program_type'] }}</td>
                <td style="text-align: left;">{{ $student['code'] }}</td>
                <td style="text-align: left;">{{ $student['hours'] }}</td>
                <td style="text-align: left;">{{ $student['retail_wholesale'] }}</td>
                <td style="text-align: left;">{{ $student['description'] }}</td>
                <td style="text-align: left;">{{ $student['displayed_on'] }}</td>
                <td style="text-align: left;">{{ $student['unite_price'] }}</td>
                <td style="text-align: left;">{{ $student['certification_included'] }}</td>
                <td style="text-align: left;">{{ $student['status'] }}</td>
            </tr>
        @endforeach
    </table>
</div>
