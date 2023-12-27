<form name="frmname" id="frmid" action="{{route('student-store')}}" method="post" enctype="multipart/form-data">
    {{csrf_field()}}
    <input type="hidden" id="duplicate_allow" name="duplicate_allow" value="0">
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive m-b-20 entry-table">
                <table class="table table-earning data-table" id="addnewtblid">
                    <thead>
                    <tr>
                        <th></th>
                        <th><strong><span class="text-danger">*</span> First Name</strong></th>
                        <th><strong><span class="text-danger">*</span> Last Name</strong></th>
                        <th><strong><span class="text-danger">*</span> Email</strong></th>
                        <th><strong><span class="text-danger">*</span> Program</strong></th>
                        <th><strong><span class="text-danger">*</span> SRP</strong></th>
                        <th><strong><span class="text-danger">*</span> Price Paid</strong></th>
                        <th><strong><span class="text-danger">*</span> Payment Type</strong></th>
                        <th><strong><span class="text-danger">*</span> Start Date</strong></th>
                        <th>End Date</th>
                        {{--<th>Complete Date</th>--}}
                        <th><strong><span class="text-danger">*</span>Phone</strong></th>
                        {{--<th>Status</th>--}}
                        <th><strong><span class="text-danger">*</span>Street</strong></th>
                        <th><strong><span class="text-danger">*</span>City</strong></th>
                        <th><strong><span class="text-danger">*</span> State</strong></th>
                        <th><strong><span class="text-danger">*</span>Zip</strong></th>
                        <th>Country</th>
                        <th></th>
                        {{--<th></th>--}}
                        <th></th>
                    </tr>
                    </thead>
                    <tbody id="student-container">
                    <tr class="student-div">
                        <td>
                            <button type="button" class="btn btn-success btn-sm add-student">+</button>
                        </td>
                        <td>
                            <div class="form-group">
                                <input type="hidden" name="student[0][id]" />
                                <input type="text" name="student[0][first_name]" id="first_name_0" class="form-control" placeholder="First Name">
                            </div>
                        </td>
                        <td>
                            <div class="form-group">
                                <input type="text" name="student[0][last_name]" id="last_name_0" class="form-control" placeholder="Last Name">
                            </div>
                        </td>
                        <td>
                            <div class="form-group">
                                <input type="text" name="student[0][email]" id="email_0" class="form-control" placeholder="Email">
                            </div>
                        </td>
                        {{--<td>
                            <div class="form-group">
                                <input type="text" class="form-control get_program" placeholder="Program" id="get_program_0" autocomplete="off">
                                <input type="hidden" name="student[0][program_id]" id="program_id_0">
                            </div>
                        </td>--}}
                        <td>
                            <select name="student[0][program_id]" id="program_id_0" class="form-control program_id">
                                <option value="">Program</option>
                                @foreach($programs as $program)
                                    <option value="{{$program->id}}" data-amount="{{$program->unite_price}}" data-wholesale="{{$program->retail_wholesale}}">{{$program->name}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <div class="form-group">
                                <input type="text" name="student[0][payment_amount]" id="payment_amount_0" class="form-control payment_amount" placeholder="SRP" readonly="readonly">
                            </div>
                        </td>
                        <td>
                            <div class="form-group">
                                <input type="text" name="student[0][price_paid]" id="price_paid_0" class="form-control price_paid" placeholder="Price Paid">
                            </div>
                        </td>
                        <td>
                            <select name="student[0][payment_type]" id="payment_type_0" class="form-control payment_type">
                                <option value="">Select Type</option>
                                @foreach(\App\Models\Student::getPaymentType() as $payment_type)
                                    <option value="{{$payment_type['actual_value']}}">{{$payment_type['display_value']}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <div class="form-label-group">
                                <input type="text" id="start_date_0" name="student[0][start_date]" class="form-control" placeholder="Start Date" autocomplete="off">
                                <label class="input-group-btn" for="start_date_0">
                                    <span class="form-date" style="top: 1px;">
                                        <i class="fa fa-calendar" aria-hidden="true"></i>
                                    </span>
                                </label>
                            </div>
                        </td>
                        <td>
                            <div class="form-label-group">
                                <input type="text" name="student[0][end_date]" id="end_date_0" class="form-control" placeholder="End Date" autocomplete="off">
                                <label class="input-group-btn" for="end_date_0">
                                    <span class="form-date" style="top: 1px;">
                                        <i class="fa fa-calendar" aria-hidden="true"></i>
                                    </span>
                                </label>
                            </div>
                        </td>
                        {{--<td>
                            <div class="form-label-group">
                                <input type="text" name="student[0][complete_date]" id="complete_date_0" class="form-control" placeholder="Complete Date" autocomplete="off">
                                <label class="input-group-btn" for="complete_date_0">
                                    <span class="form-date" style="top: 1px;">
                                        <i class="fa fa-calendar" aria-hidden="true"></i>
                                    </span>
                                </label>
                            </div>
                        </td>--}}
                        <td>
                            <div class="form-group">
                                <input type="text" name="student[0][phone]" id="phone_0" class="form-control" placeholder="Phone">
                            </div>
                        </td>
                        {{--<td>
                            <select name="student[0][status]" id="status_0" class="form-control">
                                @foreach(\App\Models\Student::getStatus() as $id => $label)
                                    <option value="{{$id}}">{{$label}}</option>
                                @endforeach
                            </select>
                        </td>--}}

                        <td>
                            <div class="form-group">
                                <input type="text" name="student[0][street]" id="street_0" class="form-control" placeholder="Street">
                            </div>
                        </td>
                        <td>
                            <div class="form-group">
                                <input type="text" name="student[0][city]" id="city_0" class="form-control" placeholder="City">
                            </div>
                        </td>
                        <td>
                            <select name="student[0][state]" id="state_0" class="form-control">
                                <option value="">Select State</option>
                                @if($states) @foreach($states as $state)
                                        <option value="{{$state->id}}">{{$state->state_name}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </td>
                        <td>
                            <div class="form-group">
                                <input type="text" name="student[0][zip]" id="zip_0" class="form-control" placeholder="Zip">
                            </div>
                        </td>
                        <td>
                            <div class="form-group">
                                {{--<input type="text" name="student[0][country]" id="country_0" class="form-control" placeholder="Country">--}}
                                <select name="student[0][country]" id="country_0" class="form-control">
                                    {{--<option value="">Select Country</option>--}}
                                    @if($countries) @foreach($countries as $country)
                                        <option value="{{$country->id}}">{{$country->country_name}}</option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                        </td>
                        {{--<td>
                            <input type="file" name="attachment_0" id="attachment_0" hidden>
                            <label for="attachment_0">
                                <i class="fa fa-upload" aria-hidden="true" style="cursor:pointer;margin-left:7px;"></i>
                            </label>
                        </td>--}}
                        {{--<td>
                            <button type="button" class="btn btn-dload btn-sm">
                                <i class="fa fa-download" aria-hidden="true"></i>
                            </button>
                        </td>--}}
                        <td>
                            <button type="button" class="btn btn-danger btn-sm remove-student">x</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <a href="{{ route('student-list') }}" class="btn btn-secondary btn-sm">Back to Student Dashboard</a>
                    <button type="reset" class="btn btn-secondary btn-sm float-right ml-1"> Reset</button>
                    <button type="submit" class="btn btn-primary btn-sm  float-right" id="submit-btn">Submit</button>
                </div>
            </div>
        </div>
    </div>
</form>
