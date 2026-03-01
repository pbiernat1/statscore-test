<?php
declare(strict_types=1);

namespace App\Domain\Response;

use App\Domain\DTO\Event\EventDTO;

final readonly class Event
{
    public function __construct(
        public ?string $status = null,
        public ?string $message = null,
        public ?EventDTO $event = null
    ) {
    }
}
