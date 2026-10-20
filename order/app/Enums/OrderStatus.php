<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Created = 'created';

    case StockReserved = 'stock_reserved';

    case PaymentPending = 'payment_pending';

    case Paid = 'paid';

    case Confirmed = 'confirmed';

    case Shipped = 'shipped';

    case Completed = 'completed';

    case PaymentFailed = 'payment_failed';

    case Cancelled = 'cancelled';
}
