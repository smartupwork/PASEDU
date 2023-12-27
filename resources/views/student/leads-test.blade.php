<table class="table table-earning data-table" id="table_id" data-total="{{count($result)}}">
    <thead>
    <tr>
        <th id="sn_sort">SNo.</th>
        <th id="inquiry_message_sort">Inquiry Message </th>
        <th id="first_name_sort">First Name</th>
        <th id="last_name_sort">Last Name</th>
        <th id="email_sort">Email</th>
        <th id="partner_institution_sort">Partner Institution</th>
        <th id="name_of_requester_sort">Name Of Requester</th>
        <th id="email_of_requester_sort">Email Of Requester</th>
        <th id="phone_sort">Phone</th>
        <th id="address_sort">Address</th>
        <th id="country_sort">Country</th>
        <th id="state_sort">State</th>
        <th id="city_sort">City</th>
        <th id="zip_sort">Zip Code</th>
        <th id="interested_program_sort">Interested Program</th>
        <th id="financing_needs_sort">Financing Needs</th>
        <th id="category_of_interest_sort">Category Of Interest</th>
        <th id="timezone_sort">Time Zone</th>
    </tr>
    </thead>
    <tbody>
    @if(count($result) > 0)
        @foreach($result as $index => $lead)
        <tr role="row" class="odd">
            <td class="sn_sort">{{$index + 1}}</td>
            <td class="inquiry_message_sort">{{ $lead->inquiry_message }}</td>
            <td class="first_name_sort">{{ $lead->firstname }}</td>
            <td class="last_name_sort">{{ $lead->lastname }}</td>
            <td class="email_sort">{{ $lead->email }}</td>
            <td class="partner_institution_sort">{{ $lead->partner_institution }}</td>
            <td class="name_of_requester_sort">{{ $lead->name_of_requester }}</td>
            <td class="email_of_requester_sort">{{ $lead->email_of_requester }}</td>
            <td class="phone_sort">{{ $lead->phone }}</td>
            <td class="address_sort">{{ $lead->address }}</td>
            <td class="country_sort">{{ $lead->country }}</td>
            <td class="state_sort">{{ $lead->state }}</td>
            <td class="city_sort">{{ $lead->city }}</td>
            <td class="zip_sort">{{ $lead->zip }}</td>
            <td class="interested_program_sort">{{ $lead->interested_program }}</td>
            <td class="financing_needs_sort">{{ $lead->financing_needs }}</td>
            <td class="category_of_interest_sort">{{ $lead->category_of_interest }}</td>
            <td class="timezone_sort">{{ $lead->timezone }}</td>
        </tr>
        @endforeach
    @else
        <tr>
            <td colspan="{{ count($column_setting['column_position']) }}" style="text-align:center;">No Record Found.</td>
        </tr>
    @endif
    </tbody>
</table>

{{--<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.24/jquery-ui.min.js"></script>
<script src="https://www.jqueryscript.net/demo/Simple-jQuery-Plugin-For-Draggable-Table-Columns-Dragtable/jquery.dragtable.js"></script>--}}

<script>

    $(document).ready(function(){

        var visible_columns = {!! json_encode($column_setting['user_columns']) !!};
        //console.log(visible_columns);
        $.each(visible_columns, function(index, item){
            $('#'+item).hide();
            $('.'+item).hide();
        });
    });
</script>