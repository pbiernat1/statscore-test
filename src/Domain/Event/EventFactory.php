<?php
declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\Event\Type\FoulEvent;
use App\Domain\Event\Type\GoalEvent;

class EventFactory
{
    public static function fromArray(array $data)
    {
        $eventClassName = sprintf('App\Domain\Event\Type\%sEvent', ucfirst($data['type']));

        if (!class_exists($eventClassName)) {
            throw new \InvalidArgumentException('Unknown EventType class: '. $eventClassName);
        }

        switch ($eventClassName) {
            case GoalEvent::class:
                return new GoalEvent(
                    $data['player'],
                    $data['assisting_player'],
                    $data['team_id'],
                    $data['match_id'],
                    $data['minute'],
                    $data['second']
                );
            case FoulEvent::class:
                return new FoulEvent(
                    $data['player'],
                    $data['affected_player'],
                    $data['team_id'],
                    $data['match_id'],
                    $data['minute'],
                    $data['second']
                );
        }
    }
}
