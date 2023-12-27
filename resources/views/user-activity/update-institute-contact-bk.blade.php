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
                        <td><strong>Contact Name</strong></td>
                        <td>{{ $data['old_data']['contact_name'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Title</strong></td>
                        <td>{{ $data['old_data']['title'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Phone</strong></td>
                        <td>{{ $data['old_data']['phone'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Email</strong></td>
                        <td>{{ $data['old_data']['email'] }}</td>
                    </tr>
                </table>
            </td>
            <td>
                <table style="width: 100%">
                    <tr>
                        <td><strong>Contact Name</strong></td>
                        <td>{{ $data['new_data']['contact_name'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Title</strong></td>
                        <td>{{ $data['new_data']['title'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Phone</strong></td>
                        <td>{{ $data['new_data']['phone'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Email</strong></td>
                        <td>{{ $data['new_data']['email'] }}</td>
                    </tr>
                </table>
            </td>
        </tr>

    </table>
</div>
