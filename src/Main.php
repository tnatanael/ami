<?php

namespace Enniel\Ami;

use Illuminate\Config\Repository;
use React\EventLoop\LoopInterface;
use Illuminate\Container\Container;
use Illuminate\Events\EventServiceProvider;
use Clue\React\Ami\Protocol\Event;
use Enniel\Ami\Providers\AmiServiceProvider;
use Illuminate\Console\Application;

class Main
{
    /**
     * @var \React\EventLoop\LoopInterface
     */
    protected $loop;

    /**
     * @var \Illuminate\Events\Dispatcher
     */
    protected $events;

    public function run()
    {
        $app = new Container();
        $app->instance('config', new Repository());
        (new EventServiceProvider($app))->register();
        (new AmiServiceProvider($app))->register();
        $this->loop = $app[LoopInterface::class];
        $this->events = $app['events'];

        $this->events->listen('ami.events.QueueCallerJoin', function (Event $event) {
            var_dump($event->getFields());
        });

        $application = new Application($app, $this->events, '5.3');
        $application->call('ami:listen');
    }
}