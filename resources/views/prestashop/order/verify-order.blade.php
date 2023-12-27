@extends('layout.main')
@section('content')

    <div class="section__content">
        <div class="container-fluid">
            <form name="order_form" id="order_form" action="{{route('order-send-to-zoho-submit')}}" method="post" enctype="multipart/form-data">
                {{csrf_field()}}
                <input type="hidden">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">Customer Detail</div>
                        <div class="card-body card-block m-3 psp-temp">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">First Name </label>
                                            <input type="text" maxlength="100" id="customer_name" name="customer[First_Name]" value="{{ $order->firstname}}" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Last Name </label>
                                            <input type="text" maxlength="100" id="customer_name" name="customer[Last_Name]" value="{{ $order->lastname}}" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Email </label>
                                            <input type="text" maxlength="100" id="customer_email" name="customer[Email]" value="{{ $order->email}}" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Phone </label>
                                            <input type="text" maxlength="100" id="customer_phone" name="customer[Phone]" value="{{ $order->phone}}" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <h3 style="margin: 30px 0 30px 0;"> Mailing Address</h3>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Street</label>
                                            <input type="text" maxlength="100" id="customer_mailing_street" name="customer[Mailing_Street]" value="{{ $order->ad_street}}" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">City </label>
                                            <input type="text" maxlength="100" id="customer_mailing_street" name="customer[Mailing_City]" value="{{ $order->ad_city}}" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">State </label>
                                            <input type="text" maxlength="100" id="customer_mailing_street" name="customer[Mailing_State]" value="{{ $order->ad_city}}" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Zip</label>
                                            <input type="text" maxlength="100" id="customer_mailing_street" name="customer[Mailing_Zip]" value="{{ $order->ad_zip}}" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Country</label>
                                            <input type="text" maxlength="100" id="customer_mailing_street" name="customer[Mailing_Country]" value="{{ $order->ad_iso_code}}" class="form-control">
                                        </div>
                                    </div>

                                </div>

                            <h3 style="margin: 30px 0 30px 0;"> Invoice Address</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="title" class="form-control-label">Street</label>
                                        <input type="text" maxlength="100" id="customer_invoice_street" name="customer[Other_Street]" value="{{ $order->ai_street}}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="title" class="form-control-label">City </label>
                                        <input type="text" maxlength="100" id="customer_invoice_street" name="customer[Other_City]" value="{{ $order->ai_city}}" class="form-control">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="title" class="form-control-label">State </label>
                                        <input type="text" maxlength="100" id="customer_invoice_street" name="customer[Other_State]" value="{{ $order->ai_city}}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="title" class="form-control-label">Zip</label>
                                        <input type="text" maxlength="100" id="customer_invoice_street" name="customer[Other_Zip]" value="{{ $order->ai_zip}}" class="form-control">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="title" class="form-control-label">Country</label>
                                        <input type="text" maxlength="100" id="customer_invoice_street" name="customer[Other_Country]" value="{{ $order->ai_iso_code}}" class="form-control">
                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="card">
                        <div class="card-header">Enrollment Detail</div>
                        <div class="card-body card-block m-3 psp-temp">




                                <div class="row">
                                    <div class="col-md-6">

                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Subject</label>
                                            <input type="text" maxlength="100" id="subject" name="enrollment[Subject]" value="{{ $order->firstname.' '.$order->lastname}}" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Contact Name</label>
                                            <input type="text" maxlength="100" id="contact_name" name="enrollment[Contact_Name]" value="{{ $order->firstname.' '.$order->lastname}}" class="form-control">
                                        </div>

                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Start Date</label>
                                            <input type="text" maxlength="100" id="start_date" name="enrollment[Start_Date]" value="{{ $order->invoice_date }}" class="form-control">
                                        </div>

                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">End Date</label>
                                            <input type="text" maxlength="100" id="end_date" name="enrollment[End_Date]" value="{{ $end_date }}" class="form-control">
                                        </div>

                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Username</label>
                                            <input type="text" maxlength="100" id="username" name="enrollment[Username]" value="{{ $order->email }}" class="form-control">
                                        </div>

                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Program</label>
                                            <input type="text" maxlength="100" id="end_date" name="enrollment[Program]" value="{{ $order->product_zoho_id }}" class="form-control">
                                        </div>

                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Adjustment</label>
                                            <input type="text" maxlength="100" id="end_date" name="enrollment[Adjustment]" value="0" class="form-control">
                                        </div>

                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Sub Total</label>
                                            <input type="text" maxlength="100" id="end_date" name="enrollment[Sub_Total]" value="" class="form-control">
                                        </div>

                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Grand Total</label>
                                            <input type="text" maxlength="100" id="grand_total" name="enrollment[Grand_Total]" value="" class="form-control">
                                        </div>

                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Status</label>
                                            <input type="text" maxlength="100" id="end_date" name="enrollment[Status]" value="Active" class="form-control">
                                        </div>

                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Account Name</label>
                                            <input type="text" maxlength="100" id="Account_Name" name="enrollment[Account_Name]" value="1066248000018564023" class="form-control">
                                        </div>

                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Layout</label>
                                            <input type="text" maxlength="100" id="Layout" name="enrollment[Layout]" value="1066248000486617525" class="form-control">
                                        </div>

                                    </div>

                                </div>

                            <div class="row">
                                <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Product ZOHO ID</label>
                                            <input type="text" name="enrollment[Ordered_Items][product][id]" value="{{ $order->product_zoho_id }}">
                                        </div>

                                </div>


                                <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Product_Code</label>
                                            <input type="text" name="enrollment[Ordered_Items][Product_Name][Product_Code]" value="{{ $zoho_data['code'] }}">
                                        </div>
                                </div>

                                <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Product ID</label>
                                            <input type="text" name="enrollment[Ordered_Items][Product_Name][id]" value="{{ $order->product_zoho_id }}">
                                        </div>
                                </div>

                                <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Product_Name</label>
                                            <input type="text" name="enrollment[Ordered_Items][Product_Name][name]" value="{{ $order->program_name}}">
                                        </div>
                                </div>

                                <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Program_Code</label>
                                            <input type="text" name="enrollment[Ordered_Items][Program_Code]" value="{{ $zoho_data['code'] }}">

                                    </div>
                                </div>

                                <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Program_Type</label>
                                            <input type="text" name="enrollment[Ordered_Items][Program_Type]" value="{{ $zoho_data['program_type'] }}">

                                    </div>
                                </div>

                                <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Hours</label>
                                            <input type="text" name="enrollment[Ordered_Items][Hours]" value="{{ $zoho_data['hours'] }}">

                                    </div>
                                </div>

                                <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Quantity</label>
                                            <input type="text" name="enrollment[Ordered_Items][Quantity]" value="1">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="title" class="form-control-label">Discount</label>
                                        <input type="text" name="enrollment[Ordered_Items][Discount]" value="">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="title" class="form-control-label">List_Price</label>
                                        <input type="text" name="enrollment[Ordered_Items][List_Price]" value="">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="title" class="form-control-label">Tax</label>
                                        <input type="text" name="enrollment[Ordered_Items][Tax]" value="0">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="title" class="form-control-label">Total</label>
                                        <input type="text" name="enrollment[Ordered_Items][Total]" value="4590">
                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="card">
                        <div class="card-header">Payment Detail</div>
                        <div class="card-body card-block m-3 psp-temp">

                                <div class="row">
                                    <div class="col-md-6">

                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Registration</label>
                                            <input type="text" maxlength="100" id="customer_name" name="payment[Registration]" value="" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Name </label>
                                            <input type="text" maxlength="100" id="customer_name" name="payment[Name]" value="{{ $order->firstname.' '.$order->lastname}}" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Contact </label>
                                            <input type="text" maxlength="100" id="customer_name" name="payment[Contact]" value="" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Payment Type</label>
                                            <input type="text" maxlength="100" id="customer_name" name="payment[Payment_Type]" value="Tuition Payment" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Paid To </label>
                                            <input type="text" maxlength="100" id="customer_name" name="payment[Paid_To]" value="World Education" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Payment Amount </label>
                                            <input type="text" maxlength="100" id="Payment_Amount" name="payment[Payment_Amount]" value="" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Payment Date</label>
                                            <input type="text" maxlength="100" id="customer_name" name="payment[Payment_Date]" value="{{ date('Y-m-d') }}" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Payment Source</label>
                                            <input type="text" maxlength="100" id="customer_name" name="payment[Payment_Source]" value="paypal" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Discount_Amount </label>
                                            <input type="text" maxlength="100" id="customer_name" name="payment[Discount_Amount]" value="0" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Promo Code PO Number</label>
                                            <input type="text" maxlength="100" id="Promo_Code_PO_Number" name="payment[Promo_Code_PO_Number]" value="" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Enrollment_Channel</label>
                                            <input type="text" maxlength="100" id="Enrollment_Channel" name="payment[Enrollment_Channel]" value="Direct Pay" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Layout</label>
                                            <input type="text" maxlength="100" id="Layout" name="payment[Layout]" value="1066248000486591725" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title" class="form-control-label">Owner</label>
                                            <input type="text" maxlength="100" id="Owner" name="payment[Owner]" value="1066248000000068001" class="form-control">
                                        </div>
                                    </div>

                                </div>

                        </div>

                    </div>

                    <div class="row">
                        <div class="col-md-12">
                           <button type="submit" class="btn btn-primary btn-sm  float-right" id="submit-btn">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>

@section('myCssFiles')
    <style>
        .fieldset{
            border:1px solid black !important;
        }
    </style>
@stop


@stop
