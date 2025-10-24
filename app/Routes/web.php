<?php
return [
    ['POST', '/api/v1/payments', [App\Controllers\PaymentController::class, 'create']],
    ['GET', '/api/v1/payments/{id}', [App\Controllers\PaymentController::class, 'show']],
    ['POST', '/api/v1/payments/callback', [App\Controllers\PaymentController::class, 'callback']],
];