<?php
declare(strict_types=1);

namespace App\Domain\Event;

use App\Infrastructure\Persistence\Event\EventStorageInterface;
use App\Infrastructure\Persistence\Statistics\StatisticsStorageInterface;
use App\Domain\DTO\Event\EventDTO;
use App\Domain\DTO\Event\EventDataDTO;
use App\Domain\Response\Event;

class EventHandler
{
    public function __construct(
        protected EventStorageInterface $eventStorage,
        protected StatisticsStorageInterface $statsStorage
    ) {
    }

    public function handleEvent(EventDataDTO $data): Event
    {
        $eventDTO = new EventDTO($data->type, time(), $data);
        $this->eventStorage->save($eventDTO);

        $eventType = match ($data->type) {
            EventDTO::TYPE_GOAL => StatisticsStorageInterface::TYPE_GOALS,
            EventDTO::TYPE_FOUL => StatisticsStorageInterface::TYPE_FOULS,
        };

        $this->statsStorage->updateTeamStatistics(
            $data->matchId,
            $data->teamId,
            $eventType
        );

        return new Event('success', 'Event saved successfully', $eventDTO);
    }
}