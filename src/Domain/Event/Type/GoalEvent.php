<?php
declare(strict_types=1);

namespace App\Domain\Event\Type;

class GoalEvent extends Event
{
    public function __construct(
        protected string $player,
        protected string $teamId,
        protected string $matchId,
        protected int $minute,
        protected int $second
    ) {
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
