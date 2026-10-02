<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Require Email Verification
    |--------------------------------------------------------------------------
    |
    | When true, new accounts must click the link emailed to them before they
    | can use the app. Only turn this off while real email sending (SMTP) is
    | not set up yet - with MAIL_MAILER=log the link is never delivered, so
    | new users would be stuck on the "Check your inbox" page.
    |
    */
    'require_email_verification' => (bool) env('REQUIRE_EMAIL_VERIFICATION', true),

    /*
    |--------------------------------------------------------------------------
    | Allowed School Email Domains
    |--------------------------------------------------------------------------
    |
    | Comma-separated list, e.g. SCHOOL_EMAIL_DOMAINS="school.edu.ph,staff.school.edu.ph".
    | When set, only emails ending in one of these domains can register, which
    | keeps outsiders out of the school's lost & found. Leave it empty to allow
    | any email address (handy for local testing).
    |
    */
    'school_email_domains' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('SCHOOL_EMAIL_DOMAINS', ''))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Lost & Found Office
    |--------------------------------------------------------------------------
    |
    | Where finders can turn items in. Once an item is received there, office
    | staff (admins) review its claims and hand it over.
    |
    */
    'office' => [
        'name' => env('LOSTMATE_OFFICE_NAME', 'Guidance Office'),
        'hours' => env('LOSTMATE_OFFICE_HOURS', 'Mon-Fri, 8:00 AM - 5:00 PM'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Unclaimed Item Policy
    |--------------------------------------------------------------------------
    |
    | Found items still "open" (nobody has claimed them) this many days after
    | they were found appear on the admin "Office & unclaimed" page, where
    | staff can close them as donated or disposed, with a required note.
    |
    */
    'unclaimed_after_days' => (int) env('LOSTMATE_UNCLAIMED_AFTER_DAYS', 60),

];
