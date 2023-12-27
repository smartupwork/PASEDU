<div class="table-responsive m-b-20">
    <table style="width:100%" class="table table-earning data-table">
        <tr style="font-size: 14px;">
            <th>S.No</th>
            <th>ZOHO Module</th>
            <th>School</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Program</th>
            <th>Payment Amount</th>
            <th>Payment Method</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Street</th>
            <th>City</th>
            <th>State</th>
            <th>Country</th>
            <th>Zip Code</th>
            <th>Lead Status</th>
            <th>Lead Stage</th>
        </tr>
        @foreach($data as $index => $student_data)
            @php
                if(isset($student_data['zoho_deals'])){
                    //dd($student_data['zoho_deals']);
                    $student = $student_data['zoho_deals'];
                    $school_name = $student['Account_Name']['name'];
                    $first_name = $student_data['zoho_contact']['First_Name'];
                    $last_name = $student_data['zoho_contact']['Last_Name'];
                    $amount = $student['Amount'];
                    $zoho_module = 'Deals';
                    //$payment_method = $student['pas_schedule'];
                }else if(isset($student_data['zoho_leads'])){
                    //dd($student_data['zoho_leads']);
                    $student = $student_data['zoho_leads'];
                    $school_name = $student['School']['name'];
                    $first_name = $student['First_Name'];
                    $last_name = $student['Last_Name'];
                    //$amount = $student['Payment_Amount'];
                    $zoho_module = 'Leads';
                    //$payment_method = $student['Payment_Method'];
                }else if(is_array($student_data) && count($student_data) > 0){
                    //dd($student_data['zoho_leads']);
                    $student = $student_data;
                    $school_name = $student_data['School']['name'];
                    $first_name = $student_data['First_Name'];
                    $last_name = $student_data['Last_Name'];
                    //$amount = $student_data['Payment_Amount'];
                    $zoho_module = 'Leads';
                    //$payment_method = $student['Payment_Method'];
                }
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $zoho_module }}</td>
                <td>{{ $school_name }}</td>
                <td>{{ $first_name }}</td>
                <td>{{ $last_name }}</td>
                <td>{{ $student['Email'] }}</td>
                <td>{{ $student['Phone'] }}</td>
                <td>{{ $student['Program']['name'] }}</td>
                <td>{{ isset($student_data['pas_schedule']) ? $student_data['pas_schedule']['payment_amount']:'' }}</td>
                <td>{{ isset($student_data['pas_schedule']) ? $student_data['pas_schedule']['payment_type']:'' }}</td>
                <td>{{ $student['Start_Date'] }}</td>
                <td>{{ $student['End_Date'] }}</td>
                <td>{{ $student['Street'] }}</td>
                <td>{{ $student['City'] }}</td>
                <td>{{ $student['State'] }}</td>
                <td>{{ $student['Country'] }}</td>
                <td>{{ isset($student_data['pas_schedule']) ? $student_data['pas_schedule']['zip']:'' }}</td>
                <td>{{ isset($student['Lead_Status']) ? $student['Lead_Status']:'' }}</td>
                <td>{{ isset($student['Lead_Stage']) ? $student['Lead_Stage']:'' }}</td>
            </tr>
        @endforeach
    </table>
</div>
