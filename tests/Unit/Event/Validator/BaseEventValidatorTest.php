<?php
declare(strict_types=1);

namespace Tests\Event\Validator;

use App\Domain\Event\Type\Event;
use App\Domain\Event\Type\GoalEvent;
use App\Domain\Event\Validator\BaseEventValidator;
use PHPUnit\Framework\TestCase;

class BaseEventValidatorTest extends TestCase
{
    private Event $event;

    protected function setUp(): void
    {
        $this->event = new GoalEvent('Test Player', 'Assisting Player', 'test_team', 'test_match', 40, 50);
    }

    public function testMissingTypeProperty()
    {
        $validator = new BaseEventValidator();
        $eventArray = $this->event->toArray();
        unset($eventArray['type']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required key: type');
        $validator->validate($eventArray);
    }

    public function testMissingPlayerProperty()
    {
        $validator = new BaseEventValidator();
        $eventArray = $this->event->toArray();
        unset($eventArray['player']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required key: player');
        $validator->validate($eventArray);
    }

    public function testMissingTeamIdProperty()
    {
        $validator = new BaseEventValidator();
        $eventArray = $this->event->toArray();
        unset($eventArray['team_id']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required key: team_id');
        $validator->validate($eventArray);
    }

    public function testMissingMatchIdProperty()
    {
        $validator = new BaseEventValidator();
        $eventArray = $this->event->toArray();
        unset($eventArray['match_id']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required key: match_id');
        $validator->validate($eventArray);
    }

    public function testMissingMinuteProperty()
    {
        $validator = new BaseEventValidator();
        $eventArray = $this->event->toArray();
        unset($eventArray['minute']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required key: minute');
        $validator->validate($eventArray);
    }

    public function testMissingSecondProperty()
    {
        $validator = new BaseEventValidator();
        $eventArray = $this->event->toArray();
        unset($eventArray['second']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required key: second');
        $validator->validate($eventArray);
    }
}
