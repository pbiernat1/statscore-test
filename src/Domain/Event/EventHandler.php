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

        // Update statistics for foul events
        if ($data->type === EventDTO::EVENT_TYPE_FOUL) {
            if (!isset($data->matchId) || !isset($data->teamId)) {
                throw new \InvalidArgumentException('match_id and team_id are required for foul events');
            }

            $this->statsStorage->updateTeamStatistics(
                $data->matchId,
                $data->teamId,
                'fouls'
            );
        }

        return new Event('success', 'Event saved successfully', $eventDTO);
    }
}