<!DOCTYPE html>
<html>
<head>
    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
            text-align: center;
        }
    </style>
</head>
<body>

<h3>To Be Inserted ({{ count($data['insert']) }})</h3>
<table style="width:100%">
    <tr>
        <th>S.No.</th>
        <th>zoho_id</th>
        <th>student_id</th>
        <th>student_zoho_id</th>
        <th>partner_id</th>
        <th>partner_zoho_id</th>
        <th>subject</th>
        <th>status</th>
        <th>grand_total</th>
        <th>start_date</th>
        <th>program_name</th>
        <th>program_zoho_id</th>
        <th>completion_date</th>
        <th>end_date</th>
        <th>final_grade</th>
        <th>username</th>
        <th>enrollment_created_at</th>
        <th>enrollment_updated_at</th>
    </tr>
    @if(count($data['insert']) > 0)
        @foreach($data['insert'] as $index => $insert)
    <tr>
        <td>{{ $index + 1 }}</td>
        <td>{{ $insert['zoho_id'] }}</td>
        <td>{{ $insert['student_id'] }}</td>
        <td>{{ $insert['student_zoho_id'] }}</td>
        <td>{{ $insert['partner_id'] }}</td>
        <td>{{ $insert['partner_zoho_id'] }}</td>
        <td>{{ $insert['subject'] }}</td>
        <td>{{ $insert['status'] }}</td>
        <td>{{ $insert['grand_total'] }}</td>
        <td>{{ $insert['start_date'] }}</td>
        <td>{{ $insert['program_name'] }}</td>
        <td>{{ $insert['program_zoho_id'] }}</td>
        <td>{{ $insert['completion_date'] }}</td>
        <td>{{ $insert['end_date'] }}</td>
        <td>{{ $insert['final_grade'] }}</td>
        <td>{{ $insert['username'] }}</td>
        <td>{{ $insert['enrollment_created_at'] }}</td>
        <td>{{ $insert['enrollment_updated_at'] }}</td>
    </tr>
        @endforeach
        @else
        <tr>
            <td colspan="17">No enrollments need to be insert</td>
        </tr>
        @endif

</table>

<h3>To Be Updated {{ count($data['update']) }}</h3>
<table style="width:100%">
    <tr>
        <th>S.No.</th>
        <th>zoho_id</th>
        <th>student_id</th>
        <th>student_zoho_id</th>
        <th>partner_id</th>
        <th>partner_zoho_id</th>
        <th>subject</th>
        <th>status</th>
        <th>grand_total</th>
        <th>start_date</th>
        <th>program_name</th>
        <th>program_zoho_id</th>
        <th>completion_date</th>
        <th>end_date</th>
        <th>final_grade</th>
        <th>username</th>
        <th>enrollment_created_at</th>
        <th>enrollment_updated_at</th>
        <th>updated_at</th>
    </tr>
    @if(count($data['update']) > 0)
        @foreach($data['update'] as $index => $insert)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $insert['zoho_id'] }}</td>
                <td>{{ $insert['student_id'] }}</td>
                <td>{{ $insert['student_zoho_id'] }}</td>
                <td>{{ $insert['partner_id'] }}</td>
                <td>{{ $insert['partner_zoho_id'] }}</td>
                <td>{{ $insert['subject'] }}</td>
                <td>{{ $insert['status'] }}</td>
                <td>{{ $insert['grand_total'] }}</td>
                <td>{{ $insert['start_date'] }}</td>
                <td>{{ $insert['program_name'] }}</td>
                <td>{{ $insert['program_zoho_id'] }}</td>
                <td>{{ $insert['completion_date'] }}</td>
                <td>{{ $insert['end_date'] }}</td>
                <td>{{ $insert['final_grade'] }}</td>
                <td>{{ $insert['username'] }}</td>
                <td>{{ $insert['enrollment_created_at'] }}</td>
                <td>{{ $insert['enrollment_updated_at'] }}</td>
                <td>{{ $insert['updated_at'] }}</td>
            </tr>
        @endforeach
    @else
        <tr>
            <td colspan="18">No enrollments need to be update</td>
        </tr>
    @endif

</table>

</body>
</html>

