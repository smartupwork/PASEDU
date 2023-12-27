<style>
    .data-table tbody tr td:last-child{ text-align:left; padding-right: 10px;}
    .data-table tbody td{ padding: 12px 10px 12px 5px;}
</style>
<div class="table-responsive m-b-20">
    <table style="widtd:100%" class="table table-earning data-table">
        <tr>
            <th style="text-align: center">
                OLD
            </th>
            <th style="text-align: center">
                NEW
            </th>
        </tr>

        <tr>
            <td>
                <table style="width: 100%">
                    <tr>
                        <td><strong>Street</strong></td>
                        <td>{{ $data['old_data']['street'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>City</strong></td>
                        <td>{{ $data['old_data']['city'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>State</strong></td>
                        <td>{{ $data['old_data']['state'] }}</td>
                    </tr>
                    <tr>
                        <td style="width: 100px;"><strong>Zip Code</strong></td>
                        <td>{{ $data['old_data']['zip_code'] }}</td>
                    </tr>
                </table>
            </td>
            <td>
                <table style="width: 100%">
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
            </td>
        </tr>

    </table>
</div>
