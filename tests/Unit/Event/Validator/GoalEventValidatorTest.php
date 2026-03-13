<?php
declare(strict_types=1);

namespace Tests\Event\Validator;

use App\Domain\Event\Type\Event;
use App\Domain\Event\Type\GoalEvent;
use App\Domain\Event\Validator\BaseEventValidator;
use App\Domain\Event\Validator\GoalEventDecorator;
use PHPUnit\Framework\TestCase;

class GoalEventValidatorTest extends TestCase
{
    private Event $event;

    protected function setUp(): void
    {
        $this->event = new GoalEvent('Test Player', 'Assisting Player', 'test_team', 'test_match', 40, 50);
    }

    public function testMissingAffectedPlayerProperty()
    {
        $validator = new BaseEventValidator();
        $validator = new GoalEventDecorator($validator);
        $eventArray = $this->event->toArray();
        unset($eventArray['assisting_player']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required key: assisting_player');
        $validator->validate($eventArray);
    }
}
