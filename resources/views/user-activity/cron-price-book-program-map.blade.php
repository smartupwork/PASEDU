@if($data['action'] == 'create')
<div class="table-responsive m-b-20">
    <table style="width:100%" class="table table-earning data-table">
        <tr style="font-size: 14px;">
            <th>S.No</th>
            <th>Price Book</th>
            <th>Program</th>
            <th>List Price</th>
        </tr>
        @foreach($data['price_book'] as $index => $price_book)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td style="text-align: left;">{{ $price_book['price_book'] }}</td>
                <td style="text-align: left;">{{ $price_book['program'] }}</td>
                <td style="text-align: left;">{{ $price_book['program_list_price'] }}</td>
            </tr>
        @endforeach
    </table>
</div>
@else
    <div class="table-responsive m-b-20">
        <table style="width:100%" class="table table-earning data-table">
            <tr style="font-size: 14px;">
                <th>S.No</th>
                <th>Price Book</th>
            </tr>
            @foreach($data['price_book'] as $index => $price_book)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="text-align: left;">{{ $price_book }}</td>
                </tr>
            @endforeach
        </table>
    </div>
@endif
