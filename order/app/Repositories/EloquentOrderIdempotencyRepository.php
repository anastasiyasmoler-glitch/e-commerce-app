<?php

namespace App\Repositories;

use App\Models\OrderIdempotencyKey;
use App\Repositories\Contracts\OrderIdempotencyRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentOrderIdempotencyRepository implements OrderIdempotencyRepositoryInterface
{
    public function lockForKey(int $userId, string $idempotencyKey): ?int
    {
        $hash = $this->hash($idempotencyKey);

        DB::table('order_idempotency_keys')->insertOrIgnore([
            'user_id' => $userId,
            'idempotency_key_hash' => $hash,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = OrderIdempotencyKey::query()
            ->where('user_id', $userId)
            ->where('idempotency_key_hash', $hash)
            ->lockForUpdate()
            ->firstOrFail();

        return $row->order_id;
    }

    public function attachOrderId(int $userId, string $idempotencyKey, int $orderId): void
    {
        $hash = $this->hash($idempotencyKey);

        OrderIdempotencyKey::query()
            ->where('user_id', $userId)
            ->where('idempotency_key_hash', $hash)
            ->update(['order_id' => $orderId]);
    }

    private function hash(string $idempotencyKey): string
    {
        return hash('sha256', $idempotencyKey);
    }
}
