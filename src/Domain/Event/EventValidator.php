<?php
declare(strict_types=1);

namespace App\Domain\Event;

class EventValidator
{
    /**
     * @param array $event
     * @throws \InvalidArgumentException
     * @return void
     */
    public function validate(array $event)
    {
        if (!isset($event['type'])) {
            throw new \InvalidArgumentException('Missing required key: type');
        }

        if (!isset($event['player'])) {
            throw new \InvalidArgumentException('Missing required key: player');
        }

        if (!isset($event['team_id'])) {
            throw new \InvalidArgumentException('Missing required key: team_id');
        }

        if (!isset($event['match_id'])) {
            throw new \InvalidArgumentException('Missing required key: match_id');
        }

        if (!isset($event['minute'])) {
            throw new \InvalidArgumentException('Missing required key: minute');
        }

        if (!isset($event['second'])) {
            throw new \InvalidArgumentException('Missing required key: second');
        }

        if (!is_int($event['minute'])) {
            throw new \InvalidArgumentException('key: minute must be an integer');
        }

        if (!is_int($event['second'])) {
            throw new \InvalidArgumentException('key: second must be an integer');
        }
    }
}
