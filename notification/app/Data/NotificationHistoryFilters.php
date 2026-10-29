<?php

namespace App\Data;

readonly class NotificationHistoryFilters
{
    public function __construct(
        public ?string $status = null,
        public ?string $channel = null,
        public ?string $recipient = null,
        public ?string $from = null,
        public ?string $to = null,
    ) {}

    public function isEmpty(): bool
    {
        return $this->status === null
            && $this->channel === null
            && ($this->recipient === null || $this->recipient === '')
            && ($this->from === null || $this->from === '')
            && ($this->to === null || $this->to === '');
    }
}
