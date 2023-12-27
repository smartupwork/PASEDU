<style>
    .data-table tbody tr td:last-child{ text-align:left; padding-right: 10px;}
    .data-table tbody td{ padding: 12px 10px 12px 5px;}
</style>
<div class="table-responsive m-b-20">
    <table style="widtd:100%" class="table table-earning data-table">
        <tr>
            <td><strong>Program</strong></td>
            <td>{{ $data->name }}</td>
        </tr>
        <tr>
            <td><strong>Code</strong></td>
            <td>{{ $data->code }}</td>
        </tr>
        <tr>
            <td><strong>Hours</strong></td>
            <td>{{ $data->hours }}</td>
        </tr>
        <tr>
            <td><strong>SRP</strong></td>
            <td>{{ $data->unite_price }}</td>
        </tr>
        <tr>
            <td><strong>Wholesale</strong></td>
            <td>{{ $data->retail_wholesale }}</td>
        </tr>
        <tr>
            <td><strong>Type</strong></td>
            <td>{{ $data->program_type }}</td>
        </tr>
        <tr>
            <td><strong>Status</strong></td>
            <td class="{{ (($data->action == "Add") ? 'text-success': ($data->action == "Remove" ? 'text-danger':''))}}">{{ $data->action }}</td>
        </tr>
        <tr>
            <td><strong>Description</strong></td>
            <td>{{ $data->description }}</td>
        </tr>
        <tr>
            <td><strong>Type</strong></td>
            <td>{{ $data->certification_included }}</td>
        </tr>

    </table>
</div>
