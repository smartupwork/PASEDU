<style>
    .data-table tbody tr td:last-child{ text-align:left; padding-right: 10px;}
    .data-table tbody td{ padding: 12px 10px 12px 5px;}
</style>
<div class="table-responsive m-b-20">
    <table style="widtd:100%" class="table table-earning data-table">
        <tr>
            <td><strong>From Name</strong></td>
            <td>{{ $data['new_data']['from_name'] }}</td>
        </tr>
        <tr>
            <td><strong>From Email</strong></td>
            <td>{{ $data['new_data']['from_email'] }}</td>
        </tr>
        <tr>
            <td><strong>Email Type</strong></td>
            <td>{{ $data['new_data']['type'] }}</td>
        </tr>
        <tr>
            <td><strong>Email Subject</strong></td>
            <td>{{ $data['new_data']['subject'] }}</td>
        </tr>
        <tr>
            <td><strong>Email Message</strong></td>
            <td>{{ $data['new_data']['message'] }}</td>
        </tr>
    </table>
</div>
