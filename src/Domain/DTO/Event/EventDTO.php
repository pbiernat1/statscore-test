<?php
declare(strict_types=1);

namespace App\Domain\DTO\Event;

final readonly class EventDTO
{
    public const EVENT_TYPE_GOAL = 'goal';

    public const EVENT_TYPE_FOUL = 'foul';
    
    public function __construct(
        public string $type,
        public int $timestamp,
        public EventDataDTO $data
    ) {
    }

    public static function fromArray(array $array): EventDTO
    {
        return new self(
            $array['type'],
            $array['timestamp'],
            EventDataDTO::fromArray($array['data'])
        );
    }
}
