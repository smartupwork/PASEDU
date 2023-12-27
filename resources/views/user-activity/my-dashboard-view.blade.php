<ol style="margin-left:10px;">
@foreach($data as $report)
    <li>{{ \App\Models\User::getHighlightReports($report) }}</li>
@endforeach
</ol>