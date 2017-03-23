<?php

namespace arls\binarystream;

use Hoa;
use React;

/**
 * Class WsServer
 *
 * HOA's WebSocket server implementation driven by a React event loop, the best of both worlds
 */
class WsServer extends Hoa\Websocket\Server {
    /**
     * @var React\EventLoop\LoopInterface
     */
    private $_loop;

    public function __construct(React\EventLoop\LoopInterface $loop, Hoa\Socket\Server $server, Hoa\Http\Request $request = null) {
        $this->_loop = $loop;
        $this->_loop->nextTick(function () {
            $this->open();
        });
        $this->_loop->addPeriodicTimer(0, function () {
            $this->serverTick();
        });

        parent::__construct($server, $request);
    }

    public function run() {
        $this->_loop->run();
    }

    public function getOriginalConnection() {
        return parent::getOriginalConnection();
    }

    protected function open() {
        $connection = $this->getConnection();

        if ($connection instanceof Hoa\Socket\Server) {
            $connection->connectAndWait();
        } else {
            $connection->connect();
        }
    }

    protected function serverTick() {
        foreach ($this->getConnection()->select() as $node) {
            if (false === is_object($node)) {
                $socket = $node;
                foreach ($this->getMergedConnections() as $other) {
                    $otherConnection = $other->getOriginalConnection();
                    if (!($otherConnection instanceof Hoa\Socket\Client)) {
                        continue;
                    }
                    $node = $otherConnection->getCurrentNode();
                    if ($node->getSocket() === $socket) {
                        $other->_run($node);
                        continue 2;
                    }
                }
            }
            foreach ($this->getMergedConnections() as $other) {
                if (true === $this->getConnection()->is($other->getOriginalConnection())) {
                    $other->_run($node);
                    return;
                }
            }
            $this->_run($node);
        }
    }
}
