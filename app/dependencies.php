<?php
declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use App\Domain\Event\EventHandler;
use App\Infrastructure\Persistence\Event\EventStorageInterface;
use App\Infrastructure\Persistence\Event\JsonFileEventStorage;
use App\Infrastructure\Persistence\Statistics\JsonFileStatisticsStorage;
use App\Infrastructure\Persistence\Statistics\StatisticsStorageInterface;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        EventStorageInterface::class => function (ContainerInterface $c) {
            $conf = $c
                ->get(SettingsInterface::class)
                ->get('eventStorage')
            ;

            return new JsonFileEventStorage($conf['jsonFilePath']);
        },
        StatisticsStorageInterface::class => function (ContainerInterface $c) {
            $conf = $c
                ->get(SettingsInterface::class)
                ->get('statisticsStorage')
            ;

            return new JsonFileStatisticsStorage($conf['jsonFilePath']);
        },
        EventHandler::class => function (ContainerInterface $c) {
            return new EventHandler(
                $c->get(EventStorageInterface::class),
                $c->get(StatisticsStorageInterface::class)
            );
        },
    ]);
};
