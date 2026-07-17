<?php

return [
    'name' => 'Staff',

    'birthday_sms_enabled' => env('STAFF_BIRTHDAY_SMS_ENABLED', true),

    'birthday_sms_message' => env('STAFF_BIRTHDAY_SMS_MESSAGE', 'Happy Birthday {name}, On behalf of the Management and entire team of Brickspoint Aparthotel, we celebrate you on this special day. Thank you for your dedication, hard work, and valuable contributions to our organization. Happy Birthday and best wishes!!!'),
];
