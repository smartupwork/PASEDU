<?php
use \App\Models\UserAccess;
use Illuminate\Support\Facades\DB;
?>
<input type="hidden" value="{{ $sort_column }}" id="sort_column">
<input type="hidden" value="{{ $sort_order }}" id="sort_order">
<table class="table table-earning data-table" id="table_id" data-total="{{ $total }}">
    <thead>
    <tr>
        <th id="load_more_sort"></th>
        <th id="first_name_sort" class="sorting" data-sort="{{ $sort_column == 's.first_name' ? $sort_order:'asc'}}" data-column="s.first_name">
            <button class="sort-by {{ ($sort_column == 's.first_name' && $sort_order == 'asc') ? '':'sort-asc'}}">First Name</button>
        </th>
        <th id="last_name_sort" class="sorting" data-sort="{{ $sort_column == 's.last_name' ? $sort_order:'asc'}}" data-column="s.last_name">
            <button class="sort-by {{ ($sort_column == 's.last_name' && $sort_order == 'asc') ? '':'sort-asc'}}">Last Name</button>
        </th>
        <th id="email_sort" class="sorting" data-sort="{{ $sort_column == 's.email' ? $sort_order:'asc'}}" data-column="s.email">
            <button class="sort-by {{ ($sort_column == 's.email' && $sort_order == 'asc') ? '':'sort-asc'}}">Email</button>
        </th>
        <th id="program_sort" class="sorting" data-sort="{{ $sort_column == 'program_name' ? $sort_order:'asc'}}" data-column="program_name">
            <button class="sort-by {{ ($sort_column == 'program_name' && $sort_order == 'asc') ? '':'sort-asc'}}">Program</button>
        </th>
        <th id="srp_sort" class="sorting" data-sort="{{ $sort_column == 's.payment_amount' ? $sort_order:'asc'}}" data-column="s.payment_amount">
            <button class="sort-by {{ ($sort_column == 's.payment_amount' && $sort_order == 'asc') ? '':'sort-asc'}}">SRP</button>
            </th>
        <th id="paid_price_sort" class="sorting" data-sort="{{ $sort_column == 's.price_paid' ? $sort_order:'asc'}}" data-column="s.price_paid">
            <button class="sort-by {{ ($sort_column == 's.price_paid' && $sort_order == 'asc') ? '':'sort-asc'}}">Paid Price</button>
            </th>
        <th id="payment_type_sort" class="sorting" data-sort="{{ $sort_column == 's.payment_type' ? $sort_order:'asc'}}" data-column="s.payment_type">
            <button class="sort-by {{ ($sort_column == 's.payment_type' && $sort_order == 'asc') ? '':'sort-asc'}}">Payment Type</button>
            </th>
        <th id="start_date_sort" class="sorting" data-sort="{{ $sort_column == 's.start_date' ? $sort_order:'asc'}}" data-column="s.start_date">
            <button class="sort-by {{ ($sort_column == 's.start_date' && $sort_order == 'asc') ? '':'sort-asc'}}">Start Date</button>
        </th>
        <th id="end_date_sort" class="sorting" data-sort="{{ $sort_column == 's.end_date' ? $sort_order:'asc'}}" data-column="s.end_date">
            <button class="sort-by {{ ($sort_column == 's.end_date' && $sort_order == 'asc') ? '':'sort-asc'}}">End Date</button>
        </th>
        <th id="phone_sort" class="sorting" data-sort="{{ $sort_column == 's.phone' ? $sort_order:'asc'}}" data-column="s.phone">
            <button class="sort-by {{ ($sort_column == 's.phone' && $sort_order == 'asc') ? '':'sort-asc'}}">Phone</button>
        </th>
        <th id="street_sort" class="sorting" data-sort="{{ $sort_column == 's.street' ? $sort_order:'asc'}}" data-column="s.street">
            <button class="sort-by {{ ($sort_column == 's.street' && $sort_order == 'asc') ? '':'sort-asc'}}">Street</button>
        </th>
        <th id="city_sort" class="sorting" data-sort="{{ $sort_column == 's.city' ? $sort_order:'asc'}}" data-column="s.city">
            <button class="sort-by {{ ($sort_column == 's.city' && $sort_order == 'asc') ? '':'sort-asc'}}">City</button>
        </th>
        <th id="state_sort" class="sorting" data-sort="{{ $sort_column == 's.state' ? $sort_order:'asc'}}" data-column="s.state">
            <button class="sort-by {{ ($sort_column == 's.state' && $sort_order == 'asc') ? '':'sort-asc'}}">State</button>
        </th>
        <th id="zip_sort" class="sorting" data-sort="{{ $sort_column == 's.zip' ? $sort_order:'asc'}}" data-column="s.zip">
            <button class="sort-by {{ ($sort_column == 's.zip' && $sort_order == 'asc') ? '':'sort-asc'}}">Zip</button>
        </th>
        <th id="country_sort" class="sorting" data-sort="{{ $sort_column == 's.country' ? $sort_order:'asc'}}" data-column="s.country">
            <button class="sort-by {{ ($sort_column == 's.country' && $sort_order == 'asc') ? '':'sort-asc'}}">Country</button>
        </th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    @if(count($result_group) > 0)
        @php $total_count_skip = 0; @endphp
        @foreach($result_group as $email => $student_g)
        @foreach($student_g as $index_child => $student)
            <?php
                $enrollments = DB::table('pas_enrollment')
                ->select([DB::raw('AES_DECRYPT(pas_contact.first_name, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS first_name'), DB::raw('AES_DECRYPT(pas_contact.last_name, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS last_name'), DB::raw('AES_DECRYPT(pas_contact.email, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS email'), DB::raw('AES_DECRYPT(pas_contact.phone, "'.$_ENV['AES_ENCRYPT_KEY'].'") AS phone'), 'program_zoho_id', 'program_name'])
                ->join('pas_contact', 'pas_contact.zoho_id', '=', 'pas_enrollment.contact_zoho_id')
                ->where('pas_enrollment.partner_id', '=', $student->partner_id)
                ->where('pas_enrollment.program_zoho_id', '=', $student->program_zoho_id)
                ->where(DB::raw('AES_DECRYPT(pas_contact.email, "'.$_ENV['AES_ENCRYPT_KEY'].'")'), '=', $student->email)
                ->where(DB::raw('AES_DECRYPT(pas_contact.first_name, "'.$_ENV['AES_ENCRYPT_KEY'].'")'), '=', $student->first_name)
                ->where(DB::raw('AES_DECRYPT(pas_contact.last_name, "'.$_ENV['AES_ENCRYPT_KEY'].'")'), '=', $student->last_name)
                ->get()->all();

            if(count($enrollments) == 0){
                ?>
            <tr role="row" class="odd {{ $index_child > 0 ? str_replace(['@', '.'], '-', $email):'' }}" id="{{ $student->id }}" style="display: {{ $index_child == 0 ? '':'none' }};">
                <td id="load_more_sort">
                    @if(count($student_g) > 1)
                        <a href="javascript:void(0)" class="more-detail" data-id="{{ pas_encrypt($student->id) }}" data-email="{{ $student->email }}" data-parent="{{ str_replace(['@', '.'], '-', $email) }}" style="display: {{ $index_child == 0 ? 'block':'none' }};">
                            <i class="fa fa-plus" aria-hidden="true"></i></a>
                    @endif
                </td>
                <td class="first_name_sort">{{$student->first_name}}</td>
                <td class="last_name_sort">{{$student->last_name}}</td>
                <td class="email_sort">{{ $student->email }}</td>
                <td class="program_sort">{{ $student->program_name }}</td>
                <td class="srp_sort">{{ $student->payment_amount }}</td>
                <td class="paid_price_sort">{{ $student->price_paid }}</td>
                <td class="payment_type_sort">{{ $student->payment_type }}</td>
                <td class="start_date_sort">{{ $student->start_date }}</td>
                <td class="end_date_sort">{{ $student->end_date }}</td>
                <td class="phone_sort">{{ $student->phone }}</td>
                <td class="street_sort">{{ $student->street }}</td>
                <td class="city_sort">{{ $student->city }}</td>
                <td class="state_sort">{{ $student->state }}</td>
                <td class="zip_sort">{{ $student->zip }}</td>
                <td class="country_sort">{{ $student->country }}</td>
                @if(UserAccess::hasAccess(UserAccess::STUDENT_MANAGEMENT_ACCESS, 'view'))
                    <td>
                        <a href="{{ route("student-detail", pas_encrypt($student->id)) }}" class="user-primary">
                            <i class="fa fa-id-card" aria-hidden="true"></i>
                        </a>
                    </td>
                @endif
            </tr>
            <?php }else{ ?>
                <input type="hidden" value="{{ $total_count_skip + 1 }}" id="total_count_skip">
            <?php } ?>

        @endforeach
        @endforeach
    @else
        <tr>
            <td colspan="{{ count($column_setting['column_position']) }}" style="text-align:center;">No Record Found.</td>
        </tr>
    @endif
    </tbody>
</table>
<script>
    $(document).ready(function(){

        var visible_columns = {!! json_encode($column_setting['user_columns']) !!};
        //console.log(visible_columns);
        $.each(visible_columns, function(index, item){
            console.log(item);
            $('#'+item).hide();
            $('.'+item).hide();
        });
    });
</script>