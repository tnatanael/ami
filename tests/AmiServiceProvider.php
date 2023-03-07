<?php

namespace Enniel\Ami\Tests;

use Enniel\Ami\Factory;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectorInterface;
use React\Socket\ConnectionInterface;
use Enniel\Ami\Tests\Factory as TestFactory;
use React\Stream\DuplexResourceStream;
use React\Stream\DuplexStreamInterface;

class AmiServiceProvider extends \Enniel\Ami\Providers\AmiServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->registerStream();
        parent::register();
    }

    /**
     * Register stream.
     */
    protected function registerStream()
    {
        $this->app->singleton(ConnectionInterface::class, function ($app) {
            return new Connection(fopen('php://temp', 'r+'), $app[LoopInterface::class]);
        });
        $this->app->alias(ConnectionInterface::class, 'ami.stream');
    }

    /**
     * {@inheritdoc}
     */
    protected function registerFactory()
    {
        $this->app->singleton(Factory::class, function ($app) {
            return new TestFactory($app[LoopInterface::class], $app[ConnectorInterface::class], $app[ConnectionInterface::class]);
        });
        $this->app->alias(Factory::class, 'ami.factory');
    }
}
