<?php
declare(strict_types=1);

namespace App\Domain\Event\Type;

class GoalEvent implements Event
{
    public function __construct(
        private string $player,
        private string $teamId,
        private string $matchId,
        private int $minute,
        private int $second
    ) {
    }

    public function getPlayer(): string
    {
        return $this->player;
    }

    public function getTeamId(): string
    {
        return $this->teamId;
    }

    public function getMatchId(): string
    {
        return $this->matchId;
    }

    public function getMinute(): int
    {
        return $this->minute;
    }

    public function getSecond(): int
    {
        return $this->second;
    }

    public function toArray(): array
    {
        return [
            'type' => 'goal',
            'player' => $this->player,
            'team_id' => $this->teamId,
            'match_id' => $this->matchId,
            'minute' => $this->minute,
            'second' => $this->second,
        ];
    }
}
