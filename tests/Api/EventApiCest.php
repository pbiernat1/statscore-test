<?php

namespace Tests\Api;

use Tests\Support\ApiTester;

class EventApiCest
{
    public function _before(ApiTester $I)
    {
        // Clean up storage files before each test
        $I->deleteFile('storage/events.txt');
        $I->deleteFile('storage/statistics.txt');
        $I->sendCommandToRedis('flushdb');
    }

    public function testFoulEvent(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/event', [
            'type' => 'foul',
            'player' => 'William Saliba',
            'team_id' => 'arsenal',
            'match_id' => 'm1',
            'minute' => 45,
            'second' => 34
        ]);

        $I->seeResponseCodeIs(201);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'status' => 'success',
            'message' => 'Event saved successfully'
        ]);
        $I->seeResponseJsonMatchesJsonPath('$.data.data.type', 'foul');
    }

    public function testFoulEventWithoutTeamIdField(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/event', [
            'type' => 'foul',
            'player' => 'William Saliba',
            'match_id' => 'm1',
            'minute' => 45,
            'second' => 34
            // Missing team_id
        ]);

        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'error' => 'Missing required key: team_id'
        ]);
    }

    public function testFoulEventWithoutMatchIdField(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/event', [
            'type' => 'foul',
            'player' => 'William Saliba',
            'team_id' => 'arsenal',
            'minute' => 45,
            'second' => 34
            // Missing match_id
        ]);

        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'error' => 'Missing required key: match_id'
        ]);
    }

    public function testFoulEventWithoutPlayerField(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/event', [
            'type' => 'foul',
            'team_id' => 'arsenal',
            'match_id' => 'm1',
            'minute' => 45,
            'second' => 34
            // Missing player
        ]);

        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'error' => 'Missing required key: player'
        ]);
    }

    public function testFoulEventWithoutMinuteField(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/event', [
            'type' => 'foul',
            'player' => 'William Saliba',
            'team_id' => 'arsenal',
            'match_id' => 'm1',
            'second' => 34
            // Missing minute
        ]);

        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'error' => 'Missing required key: minute'
        ]);
    }

    public function testFoulEventWithoutSecondField(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/event', [
            'type' => 'foul',
            'player' => 'William Saliba',
            'team_id' => 'arsenal',
            'match_id' => 'm1',
            'minute' => 54
            // Missing second
        ]);

        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'error' => 'Missing required key: second'
        ]);
    }

    public function testFoulEventWithEmptyRequestBody(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/event', []);

        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'error' => 'Missing required key: type'
        ]);
    }

    public function testInvalidJson(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/event', 'invalid json');

        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'error' => 'Invalid JSON'
        ]);
    }

    public function testEventWithoutType(ApiTester $I)
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/event', [
            'player' => 'John Doe',
            'minute' => 23,
            'second' => 34
        ]);

        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            'error' => 'Missing required key: type'
        ]);
    }
}
