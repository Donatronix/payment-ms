<?php

namespace App\Models;

class PaymentOrderBitPay extends PaymentOrder
{
    /**
     * Invoice statuses
     */
    // New
    const STATUS_INVOICE_NEW = 1000;

    // To notify merchant that an invoice has reached the status paid
    const STATUS_INVOICE_PAID_IN_FULL = 1003;

    // To notify a merchant that an invoice has expired without being paid
    const STATUS_INVOICE_EXPIRED = 1004;

    // To notify merchant that an invoice has reached the status confirmed
    const STATUS_INVOICE_CONFIRMED = 1005;

    // To notify merchant that an invoice has reached the status completed
    const STATUS_INVOICE_COMPLETED = 1006;

    // To notify merchant that an invoice has reached the status invalid
    const STATUS_INVOICE_FAILED_TO_CONFIRM = 1013;

    // To notify a merchant that a refund request has been successfully processed
    const STATUS_INVOICE_REFUND_COMPLETE = 1016;
}
