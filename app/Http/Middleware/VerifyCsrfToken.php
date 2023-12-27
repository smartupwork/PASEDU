<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/catalog/change-status',
        '/zoho/notification-callback',
        '/update-expired-status',
        '/webhook/update-enrollment',
        '/webhook/delete-enrollment',
        '/webhook/update-program',
        '/webhook/delete-program',
        '/webhook/update-price-book',
        //'/webhook/update-product',
        //'/webhook/delete-product',
        '/webhook/update-schedule',
        '/webhook/update-lead',
        '/webhook/update-lead-schedule',
        '/webhook/update-contact',
        '/webhook/update-partner',
        '/webhook/update-affiliate',
    ];
}
