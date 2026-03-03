<?php

namespace Tests;

use App\Domain\DTO\Event\EventDataDTO;
use App\Domain\DTO\Event\EventDTO;
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

        $result = $handler->handleEvent(
        new EventDataDTO(
                type: EventDTO::TYPE_GOAL,
                player: 'John Doe',
                teamId: 23,
                matchId: 34
            )
        );

        $this->assertEquals('success', $result->status);
        $this->assertEquals(EventDTO::TYPE_GOAL, $result->event->type);
        $this->assertObjectHasProperty('timestamp', $result->event);
    }

    public function testHandleEventWithoutType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Event type is required');

        $handler = new EventHandler(
            new JsonFileEventStorage($this->testFile),
            new JsonFileStatisticsStorage($this->testStatsFile)
        );

        $handler->handleEvent(new EventDataDTO());
    }

    public function testEventIsSavedToFile(): void
    {
        $storage = new JsonFileEventStorage($this->testFile);
        $handler = new EventHandler(
            $storage,
            new JsonFileStatisticsStorage($this->testStatsFile)
        );

        $handler->handleEvent(
            new EventDataDTO(type: EventDTO::TYPE_GOAL, player: 'Jane Smith', teamId: 'arsenal', matchId: 'm1')
        );

        $this->assertFileExists($this->testFile);
        $savedEvents = $storage->getAll();
        $this->assertCount(1, $savedEvents);
        $this->assertEquals(EventDTO::TYPE_GOAL, $savedEvents[0]->type);
    }

    public function testHandleFoulEventUpdatesStatistics(): void
    {
        $statsStorage = new JsonFileStatisticsStorage($this->testStatsFile);
        $handler = new EventHandler(
            new JsonFileEventStorage($this->testFile),
            $statsStorage
        );

        $result = $handler->handleEvent(
            new EventDataDTO(EventDTO::TYPE_FOUL, 'William Saliba', 'arsenal', 'm1', 45, 34)
        );

        // Check that event was saved successfully
        $this->assertEquals('success', $result->status);
        $this->assertEquals(EventDTO::TYPE_FOUL, $result->event->type);

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

        $handler->handleEvent(
            new EventDataDTO(EventDTO::TYPE_FOUL, 'John Doe', 'team_a', 'match_1', 15, 34)
        );
        $handler->handleEvent(
            new EventDataDTO(EventDTO::TYPE_FOUL, 'Jane Smith', 'team_a', 'match_1', 30, 34)
        );

        // Check that statistics were incremented correctly
        $teamStats = $statsStorage->getTeamStatistics('match_1', 'team_a');
        $this->assertEquals(2, $teamStats[StatisticsStorageInterface::TYPE_FOULS]);
    }

    public function testHandleFoulEventWithoutRequiredFields(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('match_id and team_id are required for foul events');

        $statsStorage = new JsonFileStatisticsStorage($this->testStatsFile);
        $handler = new EventHandler(
            new JsonFileEventStorage($this->testFile),
            $statsStorage
        );

        $handler->handleEvent(
            new EventDataDTO(type: EventDTO::TYPE_FOUL, player: 'John Doe', minute: 45, second: 34)
        );
    }
}