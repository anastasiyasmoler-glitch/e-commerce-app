<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;


class OrderReadService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
    ) {}

    /**
     * @return LengthAwarePaginator<int, Order>
     */
    public function listForViewer(int $viewerUserId, bool $canViewAll, int $page, int $perPage): LengthAwarePaginator
    {
        $scopeUserId = $canViewAll ? null : $viewerUserId;

        return $this->orders->paginate($scopeUserId, $page, $perPage);
    }

    public function findForViewer(int $viewerUserId, bool $canViewAll, int $orderId): ?Order
    {
        $order = $this->orders->findById($orderId);

        if ($order === null) {
            return null;
        }

        if (! $canViewAll && $order->user_id !== $viewerUserId) {
            abort(403, 'You cannot view this order.');
        }

        return $order;
    }
}
