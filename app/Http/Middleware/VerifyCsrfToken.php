<?php

namespace tcCore\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'livewire/message/preview*',
        'cms/ckeditor_upload/images*',
        'appapi/test_participant/*/hand_in',
        'appapi/test_participant/*/fraud_event',
        'appapi/version_info',
        'appapi/get_current_date',
        'appapi/feature_flags',
        'saml2/*',
        'wiris/createimage',
        'wiris/showimage',
    ];
}
