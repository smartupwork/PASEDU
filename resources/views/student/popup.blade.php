
    <input type="hidden" name="activity_progress_id" id="activity_progress_id" value="{{$activity_progress ? $activity_progress->id :''}}" />
    <input type="hidden" name="enrollment_id" id="enrollment_id" value="{{$enrollment_id}}" />
    <input type="hidden" name="activity_type" id="activity_type" value="{{$activity_type}}" />
    <input type="hidden" name="canvas_student_id" id="canvas_student_id" value="{{$canvas_student_id}}" />
    {{ csrf_field()}}
    <div class="modal-header">
        <h5 class="modal-title" id="mediumModalLabel">{{ $activity_type == 'activity-progress' ? "Activity Progress Report":"Program Progress Report" }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body">
        <div id="manually-report-container" style="display: {{ ((!$activity_progress && !$student_exists_on_canvas) ? 'block':'none') }};">
            There is no report available for this Student. Do you want to manually request this report?
            <div class="form-group">
                <button type="button" class="btn btn-primary btn-sm" id="manually-report-yes">Yes</button>
                <button type="submit" name="btnAdd" id="btnAdd" class="btn btn-secondary btn-sm" data-dismiss="modal">No</button>
            </div>
        </div>

        <div class="form-group" style="display: {{ (($activity_progress || $student_exists_on_canvas) ? 'block':'none') }};" id="scheduler-form-container">
            <div class="form-check">
                @if($student_exists_on_canvas)
                <p>
                    <label for="generate-report"> <input type="radio" name="report_type" class="form-check-input report_type" value="generate-report" id="generate-report" {{$activity_progress && $activity_progress->report_type == 'generate-report' ? "checked":""}}>Generate Report </label>
                </p>
                @endif

                <p>
                    <label class="schedule-report">
                        <input type="radio" name="report_type" class="form-check-input report_type" value="schedule-report" id="schedule-report" {{$activity_progress && $activity_progress->report_type == 'schedule-report' ? "checked":""}}>Schedule Report
                    </label>
                </p>

                <div class="pl-4" id="schedule_interval_container" style="display: {{$activity_progress && $activity_progress->report_type == 'schedule-report' ? 'block':'none'}};">
                    <p>
                        <label class="week">
                            <input type="radio" name="schedule_interval" id="week" class="form-check-input schedule_interval" value="bi-week" {{$activity_progress && $activity_progress->schedule_interval == 'bi-week' ? "checked":""}}>Bi-Weekly
                        </label>
                    </p>
                    <p>
                        <label class="one-month">
                        <input type="radio" name="schedule_interval" class="form-check-input schedule_interval" value="one-month" {{$activity_progress && $activity_progress->schedule_interval == 'one-month' ? "checked":""}}>Monthly
                        </label>
                    </p>
                    <p>
                        <label class="six-month">
                        <input type="radio" name="schedule_interval" class="form-check-input schedule_interval" value="six-month" {{$activity_progress && $activity_progress->schedule_interval == 'six-month' ? "checked":""}}>6 Months
                        </label>
                    </p>
                    <p>
                        <label for="schedule_interval">
                        <input type="radio" name="schedule_interval" id="schedule_interval" class="form-check-input schedule_interval" value="one-time" {{$activity_progress && $activity_progress->schedule_interval == 'one-time' ? "checked":""}}>One Time
                        </label>
                            <input type="text" id="scheduled_at" name="scheduled_at" class="form-control" style="width: 50%;display: {{$activity_progress && $activity_progress->schedule_interval == 'one-time' ? 'block':'none'}}" value="{{($activity_progress && !empty($activity_progress->scheduled_at)) ? date('m/d/Y', strtotime($activity_progress->scheduled_at)):''}}" autocomplete="off">
                    </p>
                    <p class="mt-2"></p>
                </div>
            </div>

            @if($activity_type == 'activity-progress')
                <div class="form-check" style="display: {{$activity_progress && $activity_progress->report_type == 'generate-report' ? "block":"none"}};" id="date-range-container">
                {{--<p>
                    <label for="fetch_report_type_all">
                        <input type="radio" name="fetch_report_type" class="form-check-input fetch_report_type" value="all" id="fetch_report_type_all" {{$activity_progress && $activity_progress->fetch_report_type == 'all' ? "checked":""}}>
                        Download All</label>
                </p>--}}

                <p>
                    <label for="fetch_report_type_date">
                        <input type="checkbox" name="fetch_report_type" class="form-check-input fetch_report_type" value="date-range" id="fetch_report_type_date" {{$activity_progress && $activity_progress->fetch_report_type == 'date-range' ? "checked":""}}>
                        Select Date</label>

                        <div style="display: {{$activity_progress && $activity_progress->fetch_report_type == 'date-range' ? 'block':'none'}}" id="fetch_report_date_range_container">

                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <div class="form-label-group">
                                        <label for="fetch_start_date" class=" form-control-label">Start Date</label>
                                        <input type="text" id="fetch_start_date" name="fetch_start_date" class="form-control" value="{{($activity_progress && !empty($activity_progress->fetch_start_date)) ? date('m/d/Y', strtotime($activity_progress->fetch_start_date)):''}}" autocomplete="off">
                                        <label class="input-group-btn" for="fetch_start_date">
                                                <span class="form-date" style="top: 40px;">
                                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                                </span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="form-group">
                                    <div class="form-label-group">
                                        <label for="fetch_end_date" class=" form-control-label">End Date</label>
                                        <input type="text" id="fetch_end_date" name="fetch_end_date" class="form-control" value="{{($activity_progress && !empty($activity_progress->fetch_end_date)) ? date('m/d/Y', strtotime($activity_progress->fetch_end_date)):''}}" autocomplete="off">
                                        <label class="input-group-btn" for="fetch_end_date">
                                                <span class="form-date" style="top: 40px;">
                                                    <i class="fa fa-calendar" aria-hidden="true"></i>
                                                </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </p>
            </div>
            @endif

            <p style="width: 50%;display: {{$activity_progress && $activity_progress->schedule_interval == 'one-time' ? 'none':'block'}}" id="is-recurring-container">
                <label for="is_recurring">
                    <input type="checkbox" name="is_recurring" id="is_recurring" {{$activity_progress && $activity_progress->is_recurring ? 'checked':''}} > Save Recurring Selection
                </label>
            </p>

        </div>
    </div>

    <div class="modal-footer" style="display: {{ (($activity_progress || $student_exists_on_canvas) ? 'block':'none') }};" >
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
        <button type="submit" name="btnAdd" id="btnAdd" class="btn btn-primary btn-sm">Submit</button>
    </div>
