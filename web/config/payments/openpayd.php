<?php

return [

    'username' => env("OPENPAYD_USERNAME","USERNAME"),
    "password" => env("OPENPAYD_PASSWORD", "PASSWORD"),
    "url" => env("OPENPAYD_URL", "https://sandbox.openpayd.com/api/"),
    "public_key_path" => storage_path('keys/openpayd.key')

];
