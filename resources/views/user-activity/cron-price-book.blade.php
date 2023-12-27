<div class="table-responsive m-b-20">
    <table style="width:100%" class="table table-earning data-table">
        <tr style="font-size: 14px;">
            <th>S.No</th>
            <th>Name</th>
            <th>Owner</th>
            <th>Description</th>
        </tr>
        @foreach($data['new_data'] as $index => $student)

            <tr>
                <td>{{ $index + 1 }}</td>
                <td style="text-align: left;">{{ $student['name'] }}</td>
                <td style="text-align: left;">{{ $student['owner'] }}</td>
                <td style="text-align: left;">{{ $student['description'] }}</td>
            </tr>
        @endforeach
    </table>
</div>
