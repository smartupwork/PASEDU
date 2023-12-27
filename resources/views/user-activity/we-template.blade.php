<div class="table-responsive m-b-20">
    <table style="width:100%" class="table table-earning data-table">
        <tr style="font-size: 14px;">
            <th>We Template Category</th>
            <td style="text-align: left;">{!! $data['parent_category_name'] !!}</td>
        </tr>
        <tr style="font-size: 14px;">
            <th>Category</th>
            <td style="text-align: left;">{!! $data['category_name'] !!}</td>
        </tr>
        <tr>
            <th>Template</th>
            <td style="text-align: left;">
                <a href="{{env('S3_PATH')}}we-templates/{{ $data['media_file']}}" target="_blank">View</a>
            </td>
        </tr>
    </table>
</div>
