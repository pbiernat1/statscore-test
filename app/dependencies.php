<?php
declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use App\Domain\Event\EventHandler;
use App\Infrastructure\Persistence\Event\EventStorageInterface;
use App\Infrastructure\Persistence\Event\JsonFileEventStorage;
use App\Infrastructure\Persistence\Event\RedisEventPublisher;
use App\Infrastructure\Persistence\Event\RedisEventStorage;
use App\Infrastructure\Persistence\Statistics\StatisticsStorageInterface;
use App\Infrastructure\Persistence\Statistics\JsonFileStatisticsStorage;
use App\Infrastructure\Persistence\Statistics\RedisStatisticsStorage;
use App\Application\Actions\EventSSEAction;
use Predis\Client as RedisClient;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        'RedisClient' => function (ContainerInterface $c) {
            $conf = $c
                ->get(SettingsInterface::class)
                ->get('redisStorage')
            ;

            return new RedisClient([
                'scheme' => 'tcp',
                'host' => $conf['redisHost'],
                'port' => $conf['redisPort']
            ]);
        },
        'RedisSubscriber' => function (ContainerInterface $c) {
            $conf = $c
                ->get(SettingsInterface::class)
                ->get('redisStorage')
            ;

            return new RedisClient([
                'scheme' => 'tcp',
                'host'   => $conf['redisHost'],
                'port'   => $conf['redisPort'],
                'read_write_timeout' => 0,  // Required for SSE
            ]);
        },
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

            // return new JsonFileEventStorage($conf['jsonFilePath']);
            return new RedisEventStorage($c->get('RedisClient'));
        },
        StatisticsStorageInterface::class => function (ContainerInterface $c) {
            $conf = $c
                ->get(SettingsInterface::class)
                ->get('statisticsStorage')
            ;

            // return new JsonFileStatisticsStorage($conf['jsonFilePath']);
            return new RedisStatisticsStorage($c->get('RedisClient'));
        },
        EventHandler::class => function (ContainerInterface $c) {
            return new EventHandler(
                $c->get(EventStorageInterface::class),
                $c->get(StatisticsStorageInterface::class)
            );
        },
        RedisEventPublisher::class => function (ContainerInterface $c) {
            return new RedisEventPublisher(
                $c->get('RedisClient'),
                $c->get('RedisSubscriber')
            );
        },
    ]);
};
