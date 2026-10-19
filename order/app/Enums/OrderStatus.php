<?php

namespace App\Enums;

/**
 * Saga lifecycle for an order (see order service spec).
 * Only a subset is implemented so far; extend as payment and shipping are added.
 */
enum OrderStatus: string
{
    case Created = 'created';

    /** Catalog reserved stock for all line items (stock.reserved). */
    case StockReserved = 'stock_reserved';

    case PaymentPending = 'payment_pending';

    case Paid = 'paid';

    case PaymentFailed = 'payment_failed';

    /** Catalog could not reserve stock or checkout was aborted (stock.reservation_failed). */
    case Cancelled = 'cancelled';
}
