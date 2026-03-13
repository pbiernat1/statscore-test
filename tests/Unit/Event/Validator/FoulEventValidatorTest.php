<?php
declare(strict_types=1);

namespace Tests\Event\Validator;

use App\Domain\Event\Type\Event;
use App\Domain\Event\Type\FoulEvent;
use App\Domain\Event\Validator\BaseEventValidator;
use App\Domain\Event\Validator\FoulEventDecorator;
use PHPUnit\Framework\TestCase;

class FoulEventValidatorTest extends TestCase
{
    private Event $event;

    protected function setUp(): void
    {
        $this->event = new FoulEvent('Test Player', 'Affected Player', 'test_team', 'test_match', 40, 50);
    }

    public function testMissingAffectedPlayerProperty()
    {
        $validator = new BaseEventValidator();
        $validator = new FoulEventDecorator($validator);
        $eventArray = $this->event->toArray();
        unset($eventArray['affected_player']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required key: affected_player');
        $validator->validate($eventArray);
    }
}
