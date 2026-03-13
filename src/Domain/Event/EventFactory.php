<?php
declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\Event\Type\Event;
use App\Domain\Event\Type\FoulEvent;
use App\Domain\Event\Type\GoalEvent;
use App\Domain\Event\Validator\BaseEventValidator;
use App\Domain\Event\Validator\FoulEventDecorator;
use App\Domain\Event\Validator\GoalEventDecorator;
use App\Domain\Event\Validator\ValidatorInterface;

class EventFactory
{
    public static function fromArray(array $data): Event
    {
        if (!isset($data['type'])) {
            throw new \InvalidArgumentException('Missing type property');
        }

        $eventClassName = static::createEventTypeClassName($data['type']);

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

    public static function createValidator(string $type): ValidatorInterface
    {
        $eventClassName = static::createEventTypeClassName($type);
        $validator = new BaseEventValidator();

        $validator = match ($eventClassName) {
            GoalEvent::class => new GoalEventDecorator($validator),
            FoulEvent::class => new FoulEventDecorator($validator),
        };

        return $validator;
    }

    private static function createEventTypeClassName(string $type): string
    {
        $eventClassName = sprintf('App\Domain\Event\Type\%sEvent', ucfirst($type));

        if (!class_exists($eventClassName)) {
            throw new \InvalidArgumentException('Unknown EventType class: '. $eventClassName);
        }

        return $eventClassName;
    }
}
