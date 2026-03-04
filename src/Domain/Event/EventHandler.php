<?php
declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\Event\Type\FoulEvent;
use App\Domain\Event\Type\GoalEvent;
use App\Infrastructure\Persistence\Event\EventStorageInterface;
use App\Infrastructure\Persistence\Statistics\StatisticsStorageInterface;
use App\Domain\Response\Event;
use App\Domain\Event\Type\Event as EventType;

class EventHandler
{
    public function __construct(
        protected EventStorageInterface $eventStorage,
        protected StatisticsStorageInterface $statsStorage
    ) {
    }

    public function handleEvent(EventType $event): Event
    {
        $this->eventStorage->save($event);

        $eventType = match (get_class($event)) {
            GoalEvent::class => StatisticsStorageInterface::TYPE_GOALS,
            FoulEvent::class => StatisticsStorageInterface::TYPE_FOULS,
        };

        $this->statsStorage->updateTeamStatistics(
            $event->getMatchId(),
            $event->getTeamId(),
            $eventType
        );

        return new Event('success', 'Event saved successfully', $event->toArray());
    }
}