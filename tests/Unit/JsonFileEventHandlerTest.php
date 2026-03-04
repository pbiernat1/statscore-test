<?php

namespace Tests;

use App\Domain\Event\Type\Event;
use App\Domain\Event\Type\GoalEvent;
use App\Domain\Event\Type\FoulEvent;
use App\Domain\Event\EventHandler;
use App\Infrastructure\Persistence\Event\JsonFileEventStorage;
use App\Infrastructure\Persistence\Statistics\JsonFileStatisticsStorage;
use App\Infrastructure\Persistence\Statistics\StatisticsStorageInterface;
use PHPUnit\Framework\TestCase;

class JsonFileEventHandlerTest extends TestCase
{
    private string $testFile;
    private string $testStatsFile;

    protected function setUp(): void
    {
        $this->testFile = sys_get_temp_dir() . '/test_events_' . uniqid() . '.txt';
        $this->testStatsFile = sys_get_temp_dir() . '/test_stats_' . uniqid() . '.txt';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }
        if (file_exists($this->testStatsFile)) {
            unlink($this->testStatsFile);
        }
    }

    public function testHandleGoalEvent(): void
    {
        $handler = new EventHandler(
            new JsonFileEventStorage($this->testFile),
            new JsonFileStatisticsStorage($this->testStatsFile)
        );

        $event = new GoalEvent(
            player: 'John Doe',
            teamId: 23,
            matchId: 34,
            minute: 1,
            second: 50
        );

        $result = $handler->handleEvent($event);

        $this->assertEquals('success', $result->status);
        $this->assertEquals(GoalEvent::class, get_class($event));
    }

    public function testEventIsSavedToFile(): void
    {
        $storage = new JsonFileEventStorage($this->testFile);
        $handler = new EventHandler(
            $storage,
            new JsonFileStatisticsStorage($this->testStatsFile)
        );

        $event = new GoalEvent('Jane Smith', 'arsenal', 'm1', 1, 1);
        $handler->handleEvent($event);

        $this->assertFileExists($this->testFile);
        $savedEvents = $storage->getAll();
        $this->assertCount(1, $savedEvents);
        $this->assertEquals(GoalEvent::class, get_class($savedEvents[0]));
    }

    public function testHandleFoulEventUpdatesStatistics(): void
    {
        $statsStorage = new JsonFileStatisticsStorage($this->testStatsFile);
        $handler = new EventHandler(
            new JsonFileEventStorage($this->testFile),
            $statsStorage
        );

        $event = new FoulEvent('William Saliba', 'arsenal', 'm1', 45, 34);
        $result = $handler->handleEvent($event);

        // Check that event was saved successfully
        $this->assertEquals('success', $result->status);
        $this->assertEquals(FoulEvent::class, get_class($event));

        // Check that statistics were updated
        $teamStats = $statsStorage->getTeamStatistics('m1', 'arsenal');
        $this->assertArrayHasKey(StatisticsStorageInterface::TYPE_FOULS, $teamStats);
        $this->assertEquals(1, $teamStats[StatisticsStorageInterface::TYPE_FOULS]);
    }

    public function testHandleMultipleFoulEventsIncrementsStatistics(): void
    {
        $statsStorage = new JsonFileStatisticsStorage($this->testStatsFile);
        $handler = new EventHandler(
            new JsonFileEventStorage($this->testFile),
            $statsStorage
        );

        $event1 = new FoulEvent('John Doe', 'team_a', 'match_1', 15, 34);
        $event2 = new FoulEvent('Jane Smith', 'team_a', 'match_1', 30, 34);

        $handler->handleEvent($event1);
        $handler->handleEvent($event2);

        // Check that statistics were incremented correctly
        $teamStats = $statsStorage->getTeamStatistics('match_1', 'team_a');
        $this->assertEquals(2, $teamStats[StatisticsStorageInterface::TYPE_FOULS]);
    }
}