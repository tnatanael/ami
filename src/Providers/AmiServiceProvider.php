<?php

namespace Enniel\Ami\Providers;

use Enniel\Ami\Factory;
use Enniel\Ami\Commands\AmiCli;
use React\Socket\Connector;
use Enniel\Ami\Commands\AmiAction;
use Enniel\Ami\Commands\AmiListen;
use React\EventLoop\LoopInterface;
use React\EventLoop\StreamSelectLoop;
use Illuminate\Support\ServiceProvider;
use React\Socket\ConnectorInterface;
use React\Dns\Resolver\Factory as DnsResolver;

class AmiServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot()
    {
        $this->publishes([
            realpath(__DIR__.'/../../config/ami.php') => config_path('ami.php'),
        ], 'ami');
    }

    /**
     * Register any package services.
     */
    public function register()
    {
        $this->registerConfig();
        $this->registerEventLoop();
        $this->registerConnector();
        $this->registerFactory();
        $this->registerAmiListen();
        $this->registerAmiAction();
        $this->registerAmiCli();
        $this->commands([
            'command.ami.listen',
            'command.ami.action',
            'command.ami.cli',
        ]);
    }

    /**
     * Register the configuration.
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(realpath(__DIR__.'/../../config/ami.php'), 'ami');
    }

    /**
     * Register the ami listen command.
     */
    protected function registerAmiListen()
    {
        $this->app->singleton(AmiListen::class, function ($app) {
            return new AmiListen($app['events'], $app['ami.eventloop'], $app['ami.factory'], $app['config']['ami']);
        });
        $this->app->alias(AmiListen::class, 'command.ami.listen');
    }

    /**
     * Register the ami listen command.
     */
    protected function registerAmiCli()
    {
        $this->app->singleton(AmiCli::class, function ($app) {
            return new AmiCli($app['events'], $app['ami.eventloop'], $app['ami.factory'], $app['config']['ami']);
        });
        $this->app->alias(AmiCli::class, 'command.ami.cli');
    }

    /**
     * Register the ami action sender.
     */
    protected function registerAmiAction()
    {
        $this->app->singleton(AmiAction::class, function ($app) {
            return new AmiAction($app['events'], $app['ami.eventloop'], $app['ami.factory'], $app['config']['ami']);
        });
        $this->app->alias(AmiAction::class, 'command.ami.action');
    }

    /**
     * Register event loop.
     */
    protected function registerEventLoop()
    {
        $this->app->singleton(LoopInterface::class, function () {
            return new StreamSelectLoop();
        });
        $this->app->alias(LoopInterface::class, 'ami.eventloop');
    }

    /**
     * Register connector.
     */
    protected function registerConnector()
    {
        $this->app->singleton(ConnectorInterface::class, function ($app) {
            $loop = $app[LoopInterface::class];

            return new Connector($loop);
        });
        $this->app->alias(ConnectorInterface::class, 'ami.connector');
    }

    /**
     * Register factory.
     */
    protected function registerFactory()
    {
        $this->app->singleton(Factory::class, function ($app) {
            return new Factory($app[LoopInterface::class], $app[ConnectorInterface::class]);
        });
        $this->app->alias(Factory::class, 'ami.factory');
    }
}
