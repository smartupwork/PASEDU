@extends('layout.main')
@section('content')

<div class="section__content section__content--p30">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        Leads View
                    </div>
                    <form name="frmname" id="frmid" method="post" action="{{ route('leadssubmit')}}" >
                        <input type="hidden" name="id" id="id" value="{{ pas_encrypt($lead->id) }}" />
                        {{ csrf_field()}}
                        <div class="card-body card-block m-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="first-Name" class=" form-control-label">Partner Institution</label>
                                        <input type="text" maxlength="150" value="{{$lead->partner_institution}}" id="partner_institution" name="partner_institution"class="form-control" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="last-name" class=" form-control-label">Name of Requester</label>
                                        <input type="text" maxlength="150" id="name_requester" value="{{$lead->name_of_requester}}" name="name_requester" class="form-control" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="email" class=" form-control-label">Email of Requester</label>
                                        <input type="text" maxlength="120" id="email_requester" value="{{$lead->email_of_requester}}" name="email_requester" class="form-control" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone" class="form-control-label">First Name</label>
                                        <input type="text" value="{{$lead->firstname}}" class="form-control" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone" class="form-control-label">Last Name</label>
                                        <input type="text" value="{{$lead->lastname}}" class="form-control" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone" class="form-control-label">Email</label>
                                        <input type="text" value="{{$lead->email}}" class="form-control" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone" class="form-control-label">Address</label>
                                        <textarea class="form-control" readonly>{{$lead->address}}</textarea>

                                    </div>

                                    <div class="form-group">
                                        <label for="phone" class="form-control-label">Inquiry Message</label>
                                        <textarea rows="3" class="form-control" readonly>{{$lead->inquiry_message}}</textarea>
                                    </div>

                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone" class="form-control-label">Phone</label>
                                        <input type="text" value="{{$lead->phone}}" class="form-control" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="city" class="form-control-label">City</label>
                                        <input type="text" maxlength="55" class="form-control" value="{{$lead->city}}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="state" class="form-control-label">State</label>
                                        <input type="text" class="form-control" value="{{$lead->state}}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="zip" class="form-control-label">Zip</label>
                                        <input type="text" class="form-control" value="{{$lead->zip}}" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="status" class=" form-control-label">Country</label>
                                        <input type="text" class="form-control" value="{{$lead->country}}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone" class="form-control-label">Interested Program</label>
                                        <input type="text" class="form-control" value="{{$lead->interested_program}}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone" class="form-control-label">Financing Needs</label>
                                        <input type="text" class="form-control" value="{{$lead->financing_needs}}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone" class="form-control-label">Category of Interest</label>
                                        <input type="text" class="form-control get_program" value="{{$lead->category_of_interest}}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone" class="form-control-label">Time Zone</label>
                                        <input type="text" class="form-control get_program" value="{{$lead->timezone}}" readonly>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-12">
                                    <a href="{{route('leads')}}" class="btn btn-secondary btn-sm">Back to Leads List
                                    </a>

                                </div>
                            </div>


                        </div>
                    </form>
                </div>

            </div>
        </div>

    </div>
</div>

@stop