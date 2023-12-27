<style>
    .data-table tbody tr td:last-child{ text-align:left; padding-right: 10px;}
    .data-table tbody td{ padding: 12px 10px 12px 5px;}
</style>
<div class="table-responsive m-b-20">
    <table style="widtd:100%" class="table table-earning data-table">
        <tr>
            <td><strong>Street</strong></td>
            <td>{{ $data['new_data']['street'] }}</td>
        </tr>
        <tr>
            <td><strong>City</strong></td>
            <td>{{ $data['new_data']['city'] }}</td>
        </tr>
        <tr>
            <td><strong>State</strong></td>
            <td>{{ $data['new_data']['state'] }}</td>
        </tr>
        <tr>
            <td style="width: 100px;"><strong>Zip Code</strong></td>
            <td>{{ $data['new_data']['zip_code'] }}</td>
        </tr>
    </table>
</div>
