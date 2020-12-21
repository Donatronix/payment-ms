<?php

$router->get('/tests/payments/', '\App\Http\Controllers\PagesController@index');

/* =================
 * P A y m e n t s
==================== */

$router->get('/tests/payments/payments/system/index', function () {
    return \App\Http\Controllers\TestController::viewMake("tests.payment.system.index");
});

$router->get('/tests/payments/payments/invoices/charge', function () {
    return \App\Http\Controllers\TestController::viewMake("tests.payment.invoice.bitpay.store");
});

/* =================
 * Version
==================== */

$router->get('/tests/payments/version', function () {
    return \App\Http\Controllers\TestController::viewMake("tests.version");
});
