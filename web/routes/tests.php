<?php

$router->get('/tests/payments/', '\App\Http\Controllers\PagesController@index');

/* =================
 * P A y m e n t s
==================== */

$router->get('/tests/payments/system/index', function () {
    return \App\Http\Controllers\TestController::viewMake("tests.payment.system.index");
});

$router->get('/tests/payments/invoices/recharge/', function () {
    return \App\Http\Controllers\TestController::viewMake("tests.payment.invoice.store");
});

$router->get('/tests/payments/lost', function () {
    return \App\Http\Controllers\TestController::viewMake("tests.payment.lost.index");
});

/* =================
 * Full Test.
==================== */

$router->get('/tests/full', function () {
    return \App\Http\Controllers\TestController::viewMake("tests.full");
});
$router->get('/tests/full-stripe', function () {
    return \App\Http\Controllers\TestController::viewMake("tests.full-stripe");
});
$router->get('/tests/end', function () {
    return \App\Http\Controllers\TestController::viewMake("tests.end");
});

/* =================
 * Version
==================== */

$router->get('/tests/payments/version', function () {
    return \App\Http\Controllers\TestController::viewMake("tests.version");
});
