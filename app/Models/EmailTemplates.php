<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplates extends Model
{
    use HasFactory;

    protected $table = 'pas_email_templates';

    const PARTNER_REGISTRATION = 1;
    const WE_USER_REGISTRATION = 2;
    const DISABLE_ACCOUNT_WRONG_PASSWORD = 3;
    const ENABLE_ACCOUNT_WRONG_PASSWORD = 4;
    const PROGRESS_REQUEST_YES = 5;
    const PROGRESS_REQUEST_NO = 11;
    const ZOHO_PERSONAL_DETAIL_UPDATED = 6;
    const ZOHO_LEADS_DETAIL_UPDATED_FOR_PARTNER = 7;
    const COLLATERAL_REQUEST = 8;
    const ONE_TIME_PASSWORD = 9;
    const LOGIN_CODE = 10;

    const STUDENT_ENROLLMENT = 12;
    const LEADS_ENTRY = 13;
    const STUDENT_BULK_ENROLLMENT = 14;
    const LEADS_ENTRY_TO_OWNER = 15;
    const LEADS_CONVERTED_TO_SALE = 16;
    const STUDENT_ENROLLMENT_NEW = 17;

}
