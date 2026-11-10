<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Showroom identity
    |--------------------------------------------------------------------------
    |
    | These values appear in the header, footer, and WhatsApp links. Change
    | them here instead of repeating the studio details in every view.
    |
    */

    'name' => env('SHOWROOM_NAME', 'Auto Museum'),

    'tagline' => env('SHOWROOM_TAGLINE', 'A private studio for cars worth a closer look.'),

    'phone' => env('SHOWROOM_PHONE', '+8801700000000'),

    'phone_display' => env('SHOWROOM_PHONE_DISPLAY', '01700-000000'),

    'whatsapp' => env('SHOWROOM_WHATSAPP', '8801700000000'),

    'email' => env('SHOWROOM_EMAIL', 'studio@automuseum.test'),

    'address' => env('SHOWROOM_ADDRESS', 'Gulshan, Dhaka'),

    'hours' => env('SHOWROOM_HOURS', 'Sat–Thu, 10:00–20:00'),

];
