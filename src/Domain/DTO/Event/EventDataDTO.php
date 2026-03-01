<?php
declare(strict_types=1);

namespace App\Domain\DTO\Event;

final readonly class EventDataDTO
{
    public function __construct(
        public ?string $type = null,
        public ?string $player = null,
        public ?string $teamId = null,
        public ?string $matchId = null,
        public ?int $minute = null,
        public ?int $second = null
    ) {
        if (empty($type)) {
            throw new \InvalidArgumentException('Event type is required');
        }
    }

    public static function fromArray(array $array): EventDataDTO
    {
        return new self(
            $array['type'],
            $array['player'],
            $array['teamId'],
            $array['matchId'],
            $array['minute'],
            $array['second']
        );
    }
}
