<?php

namespace Enniel\Ami;

use Clue\React\Ami\Client;
use Illuminate\Support\Arr;
use Clue\React\Ami\ActionSender;
use Illuminate\Support\Facades\Log;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\ConnectorInterface;

class Factory
{
    /**
     * @var \React\EventLoop\LoopInterface
     */
    protected $loop;

    /**
     * @var \React\Socket\ConnectorInterface
     */
    protected $connector;

    /**
     * @param \React\EventLoop\LoopInterface         $loop
     * @param \React\Socket\ConnectorInterface $connector
     */
    public function __construct(LoopInterface $loop, ConnectorInterface $connector)
    {
        $this->connector = $connector;
        $this->loop = $loop;
    }

    /**
     * Create client.
     *
     * @param array $options
     *
     * @return \React\Promise\Promise
     */
    public function create(array $options = [])
    {
        foreach (['host', 'port', 'username', 'secret'] as $key) {
            $options[$key] = Arr::get($options, $key, null);
        }
        
        $promise = $this->connector->connect($options['host'].':'.$options['port'])->then(function (ConnectionInterface $stream) {
            Log::info('FullFiled');
            return new Client($stream, new Parser());
        }, function($reason) {
            Log::info($reason);
        });

        if (!is_null($options['username'])) {
            $promise = $promise->then(function (Client $client) use ($options) {
                $sender = new ActionSender($client);

                return $sender->login($options['username'], $options['secret'])->then(
                    function () use ($client) {
                        return $client;
                    },
                    function ($error) use ($client) {
                        $client->close();
                        throw $error;
                    }
                );
            }, function ($error) {
                throw $error;
            });
        }

        return $promise;
    }
}
