<?php

namespace App\Domain\Event;

use App\Infrastructure\Persistence\Event\EventStorageInterface;
use App\Infrastructure\Persistence\Event\JsonFileEventStorage;
use App\Infrastructure\Persistence\Statistics\StatisticsStorageInterface;
use App\Infrastructure\Persistence\Statistics\JsonFileStatisticsStorage;

class EventHandler
{
    private EventStorageInterface $storage;
    private StatisticsStorageInterface $statisticsStorage;

    public function __construct(string $storagePath, ?StatisticsStorageInterface $statisticsManager = null)
    {
        $this->storage = new JsonFileEventStorage($storagePath);
        $this->statisticsStorage = $statisticsManager ?? new JsonFileStatisticsStorage(__DIR__ . '/../../../storage/statistics.txt');
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

        $this->storage->save($event);

        // Update statistics for foul events
        if ($data['type'] === 'foul') {
            if (!isset($data['match_id']) || !isset($data['team_id'])) {
                throw new \InvalidArgumentException('match_id and team_id are required for foul events');
            }

            $this->statisticsStorage->updateTeamStatistics(
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