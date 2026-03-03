<?php

namespace App\Infrastructure\Persistence\Statistics;

use App\Domain\DTO\Event\EventDTO;
use Predis\Client as RedisClient;

class RedisStatisticsStorage implements StatisticsStorageInterface
{
    private const PATTERN = 'football:stats:%s:%s';

    public function __construct(private RedisClient $redis)
    {
    }

    public function updateTeamStatistics(string $matchId, string $teamId, string $eventType, int $value = 1): void
    {
        $key = $this->buildKey($matchId, $teamId);

        switch ($eventType) {
            case static::TYPE_GOALS:
                $this->redis->hincrby($key,  self::TYPE_GOALS, 1);
            break;
            case static::TYPE_FOULS:
                $this->redis->hincrby($key,  self::TYPE_FOULS, 1);
            break;
        }
    }

    public function getMatchStatistics(string $matchId): array
    {
        $key = $this->buildKey($matchId, '*');
        $keys = $this->redis->keys($key);
        $teams = [];

        foreach ($keys as $key) {
            $parts = explode(':', $key);
            $teamId = end($parts);
            $stats = $this->redis->hgetall($key);

            $teams[$teamId] = $this->castIntegers($stats);
        }

        return [
            'match_id' => $matchId,
            'teams' => $teams,
        ];
    }

    public function getTeamStatistics(string $matchId, string $teamId): array
    {
        $key = $this->buildKey($matchId, $teamId);
        $stats = $this->redis->hgetall($key);

        return [
            'match_id' => $matchId,
            'team_id' => $teamId,
            'stats' => $this->castIntegers($stats),
        ];
    }

    private function buildKey(string $matchId, string $teamId): string
    {
        return sprintf(self::PATTERN, $matchId, $teamId);
    }

    private function castIntegers(array $stats): array
    {
        return array_map(static fn ($v) => (int) $v, $stats);
    }
}
