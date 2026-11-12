<?php

/** Contact details shown in the landing page footer. Set them in .env; empty values are simply not shown. */
return [
    'contact_email'   => env('CONTACT_EMAIL', env('MAIL_FROM_ADDRESS')),
    'contact_phone'   => env('CONTACT_PHONE'),
    'contact_address' => env('CONTACT_ADDRESS', 'School Equipment Office'),
];
