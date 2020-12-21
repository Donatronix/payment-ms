<?php

namespace App\Models;

class PaymentOrderPaypal extends PaymentOrder
{
    /**
     * Order / Invoice statuses
     */
    // The order was created with the specified context
    const STATUS_ORDER_CREATED = 1;

    // The order was saved and persisted. The order status continues to be in progress until
    // a capture is made with final_capture = true for all purchase units within the order.
    const STATUS_ORDER_SAVED = 2;

    // The customer approved the payment through the PayPal wallet or another form of guest
    // or unbranded payment. For example, a card, bank account, or so on.
    const STATUS_ORDER_APPROVED = 3;

    // All purchase units in the order are voided.
    const STATUS_ORDER_VOIDED = 4;

    // The payment was authorized or the authorized payment was captured for the order
    const STATUS_ORDER_COMPLETED = 5;

    // The order requires an action from the payer (e.g. 3DS authentication).
    // Redirect the payer to the "rel":"payer-action" HATEOAS link returned as part
    // of the response prior to authorizing or capturing the order.
    const STATUS_ORDER_PAYER_ACTION_REQUIRED = 6;
}
