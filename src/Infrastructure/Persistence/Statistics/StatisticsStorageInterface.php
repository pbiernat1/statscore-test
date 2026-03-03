<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Statistics;

interface StatisticsStorageInterface
{

    public const TYPE_GOALS = 'goals';

    public const TYPE_FOULS = 'fouls';

    public function updateTeamStatistics(string $matchId, string $teamId, string $eventType, int $value = 1): void;

    public function getTeamStatistics(string $matchId, string $teamId): array;

    public function getMatchStatistics(string $matchId): array;
}
