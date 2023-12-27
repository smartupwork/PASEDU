@extends('layout.main')
@section('content')

            <div class="section__content">
                    <div class="container-fluid">
                        <h2 class="page-titel m-b-20">Student Enrollment</h2>


                        <form name="frmname" id="frmid" action="{{route('student-store')}}" method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive m-b-20 entry-table">
                                        <table class="table table-earning data-table" id="addnewtblid">
                                            <thead>
                                            <tr>
                                                <th>First Name</th>
                                                <th>Last Name</th>
                                                <th>Email</th>
                                                <th>Program</th>
                                                <th>Payment Amount</th>
                                                <th>Payment Type</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>Phone</th>
                                                <th>Street</th>
                                                <th>City</th>
                                                <th>State</th>
                                                <th>Zip</th>
                                                <th>Country</th>
                                                <th></th>
                                            </tr>
                                            </thead>
                                            <tbody id="student-container">
                                            <tr class="student-div" id="{{ pas_encrypt($student->id) }}">
                                                <td>
                                                    <div class="form-group">
                                                        <input type="hidden" name="student[0][id]" />
                                                        <input type="text" name="student[0][first_name]" id="first_name_0" class="form-control" placeholder="First Name" value="{{$student->first_name}}">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <input type="text" name="student[0][last_name]" id="last_name_0" class="form-control" placeholder="Last Name" value="{{$student->last_name}}">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" value="{{$student->email}}">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <input type="text" class="form-control get_program" placeholder="Program" id="get_program_0" autocomplete="off" value="{{$student->program_name}}">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <input type="text" class="form-control get_program" placeholder="Program" id="get_program_0" autocomplete="off" value="{{$student->payment_amount}}">
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" value="{{ $student->payment_type}}">
                                                </td>
                                                <td>
                                                    <div class="form-label-group">
                                                        <input type="text" id="start_date_0" name="student[0][start_date]" class="form-control" placeholder="Start Date" autocomplete="off" value="{{$student->start_date}}">
                                                        <label class="input-group-btn" for="start_date_0">
                                    <span class="form-date" style="top: 1px;">
                                        <i class="fa fa-calendar" aria-hidden="true"></i>
                                    </span>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-label-group">
                                                        <input type="text" name="student[0][end_date]" class="form-control" value="{{ $student->end_date }}">
                                                        <label class="input-group-btn" for="end_date_0">
                                    <span class="form-date" style="top: 1px;">
                                        <i class="fa fa-calendar" aria-hidden="true"></i>
                                    </span>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" value="{{ $student->phone}}">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" value="{{ $student->street}}">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" value="{{ $student->city}}">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" value="{{ $student->state}}">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" value="{{ $student->zip }}">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        {{--<input type="text" name="student[0][country]" id="country_0" class="form-control" placeholder="Country">--}}
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" value="{{ $student->country}}">
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <a href="{{ route('student-list') }}" class="btn btn-secondary btn-sm">Back to Student Dashboard</a>

                                </div>
                            </div>

                        </form>

                        @include('layout.dashboard.footer')
                    </div>
                </div>

    @section('myJsFile')

        <script>
            $(document).ready(function(){
                $("#frmid :input").prop("disabled", true);
                $(document).on('click', '.download-voucher', function(){
                    window.location.href = '/student/download-voucher/' + $(this).parents('tr').attr('id');
                });
            });

        </script>
    @stop
@stop

