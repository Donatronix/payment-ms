<?php

use Illuminate\Support\Facades\File;

return (static function () {
    $configs = [];

    // Reading each files and join to config
    $files = File::files(base_path('config/payments/'));
    foreach ($files as $file) {
        $configs[$file->getBasename('.' . $file->getExtension())] = include $file->getRealPath();
    }

    return $configs;
})();
