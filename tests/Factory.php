<?php

namespace Enniel\Ami\Tests;

use Enniel\Ami\Parser;
use Clue\React\Ami\Client;
use React\Promise;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\ConnectorInterface;

class Factory extends \Enniel\Ami\Factory
{
    /**
     * @param \React\EventLoop\LoopInterface         $loop
     * @param \React\Socket\ConnectorInterface       $connector
     * @param \React\Stream\ConnectionInterface     $stream
     */

    protected $stream;

    public function __construct(LoopInterface $loop, ConnectorInterface $connector, ConnectionInterface $stream)
    {
        parent::__construct($loop, $connector);
        $this->stream = $stream;
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
        return Promise\resolve(new Client($this->stream, new Parser()));
    }
}
