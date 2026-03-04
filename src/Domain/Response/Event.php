<?php
declare(strict_types=1);

namespace App\Domain\Response;

final readonly class Event
{
    public function __construct(
        public string $status,
        public string $message,
        public array $data
    ) {
    }
}
