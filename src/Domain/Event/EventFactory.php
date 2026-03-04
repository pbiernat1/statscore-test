<?php
declare(strict_types=1);

namespace App\Domain\Event;

class EventFactory
{
    public static function fromArray(array $data)
    {
        $eventClassName = sprintf('App\Domain\Event\Type\%sEvent', ucfirst($data['type']));

        if (!class_exists($eventClassName)) {
            throw new \InvalidArgumentException('Unknown EventType class: '. $eventClassName);
        }

        return new $eventClassName(
            $data['player'],
            $data['team_id'],
            $data['match_id'],
            $data['minute'],
            $data['second']
        );
    }
}
