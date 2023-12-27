<div style="border:2px solid #666; padding:10px; font-family: arial, sans-serif;">
    <div style="text-align:center">
        <img src="{{public_path('images/logo.png')}}" style="width:400px" alt=""/>
    </div>

    <div style="position: relative;">
        <div style="position:absolute;top:0;width:100%">
            <h2 style="padding:10px; font-family: arial, sans-serif;font-size: 16px;">Program Progress Report</h2>
        </div>
    </div>

    <div style="position: relative;">
        <div style="position:absolute;top:0;width:100%">
            <h2 style="padding:10px; font-family: arial, sans-serif;font-size: 16px;">Student Information</h2>
            <table style="width:70%;margin:0 auto;border-collapse: collapse;border:1px solid #333;">
                <tr>
                    <td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:15%;text-align:left">Name</td>
                    <td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:15%;text-align:left">{{ $enrollment->subject }}</td>
                </tr>
                <tr>
                    <td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:15%;text-align:left">Email</td>
                    <td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:15%;text-align:left">{{ $enrollment->email }}</td>
                </tr>
                <tr>
                    <td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:15%;text-align:left">Phone</td>
                    <td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:15%;text-align:left">{{$enrollment->phone}}</td>
                </tr>
            </table>
        </div>
    </div>

    <div style="position: relative;">
        <div style="position:absolute;top:0;width:100%">
            <h2 style="padding:10px; font-family: arial, sans-serif;font-size: 16px;">Program Information</h2>
            <table style="width:70%;margin:0 auto;border-collapse: collapse;border:1px solid #333;">
                <tr>
                    <td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:15%;font-weight:bold;text-align:left">Title</td>
                    <td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:15%;font-weight:bold;text-align:left">{{ $enrollment->program_name }}</td>
                </tr>
                <tr>
                    <td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:15%;font-weight:bold;text-align:left">Duration</td>
                    <td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:15%;font-weight:bold;text-align:left">{{ \App\Utility::getMonthsInTwoDates($enrollment->start_date, $enrollment->end_date) }}</td>
                </tr>
                <tr>
                    <td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:15%;font-weight:bold;text-align:left">Hours</td>
                    <td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:15%;font-weight:bold;text-align:left">{{$enrollment->hours}} Hours</td>
                </tr>
                <tr>
                    <td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:15%;font-weight:bold;text-align:left">Start Date</td>
                    <td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:15%;font-weight:bold;text-align:left">{{ date('F, d Y', strtotime($enrollment->start_date)) }}</td>
                </tr>
                <tr>
                    <td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:15%;font-weight:bold;text-align:left">End Date</td>
                    <td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:15%;font-weight:bold;text-align:left">
                        {{ !empty($enrollment->end_date) ? date('F, d Y', strtotime($enrollment->end_date)):'--' }}
                    </td>
                </tr>
                {{--<tr>
                    <td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:15%;font-weight:bold;text-align:left">School</td>
                    <td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width:15%;font-weight:bold;text-align:left">{{ $enrollment->partner_name }}</td>
                </tr>--}}
            </table>
        </div>
    </div>

    <div style="position: relative;">
        <div style="position:absolute;top:0;width:100%">
            <h2 style="padding:10px; font-family: arial, sans-serif;font-size: 16px;">Pacing Information</h2>
            <table style="width:100%;margin:0 auto;border-collapse: collapse;border:1px solid #333;">
        <tr>
            <th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;font-weight:bold;text-align:center" width="38%">Course</th>
            <th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;font-weight:bold;text-align:center" width="35%">Completed / Total Modules</th>
            <th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;font-weight:bold;text-align:center" width="2%">Grade</th>
            <th style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;font-weight:bold;text-align:center" width="25%">Percentage (%) Complete</th>
        </tr>

@if(count($course_module_list) > 0)
    @foreach($course_module_list as $val){
          {{--@if(isset($val['completed']))--}}
    @php
        /*$can_req = new \App\Console\Commands\Canvas\CanvasRequest();
        $can_req->course_id = $val->course_id;
        $courses_activity_time = \App\CanvasHelper::getInstance()->getCoursesDetail($can_req);*/
    @endphp
    <tr>
        <td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;">{{ $val['name']}}</td>
        <td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333; text-align: center;">{{ $val['completed']}}/{{ $val['items_count']  }}</td>
        <td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333;width: 120px; text-align: center;">{{ !empty($val['grade']) ? $val['grade']:'N/A' }}</td>
        <td style="padding:5px; font-family: arial, sans-serif;font-size: 14px;border-right:1px solid #333;border-bottom:1px solid #333; text-align: center;">{{ isset($val['completed']) ? round($val['completed'] / $val['items_count'] * 100):'' }}</td>
        </tr>
        {{--@endif--}}
    @endforeach
@else
    <tr>
        <td colspan="4" style="text-align:center;">Student has no activity yet.</td>
    </tr>
@endif
            </table>
            </div>
        </div>
</div>
