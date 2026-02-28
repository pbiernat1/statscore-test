<?php
declare(strict_types=1);

namespace App\Domain\Event;

use App\Infrastructure\Persistence\Event\EventStorageInterface;
use App\Infrastructure\Persistence\Statistics\StatisticsStorageInterface;

class EventHandler
{
    public function __construct(
        protected EventStorageInterface $eventStorage,
        protected StatisticsStorageInterface $statsStorage
    ) {
    }

    public function handleEvent(array $data): array
    {
        if (!isset($data['type'])) {
            throw new \InvalidArgumentException('Event type is required');
        }

        $event = [
            'type' => $data['type'],
            'timestamp' => time(),
            'data' => $data
        ];

        $this->eventStorage->save($event);

        // Update statistics for foul events
        if ($data['type'] === 'foul') {
            if (!isset($data['match_id']) || !isset($data['team_id'])) {
                throw new \InvalidArgumentException('match_id and team_id are required for foul events');
            }

            $this->statsStorage->updateTeamStatistics(
                $data['match_id'],
                $data['team_id'],
                'fouls'
            );
        }

        return [
            'status' => 'success',
            'message' => 'Event saved successfully',
            'event' => $event
        ];
    }
}