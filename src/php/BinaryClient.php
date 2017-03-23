<?php

namespace arls\binarystream;

use Ratchet;
use React;

class Client {
    use BinaryHandlers;

    private $_url;

    public function __construct($url) {
        $this->_url = $url;
    }

    public function run(React\EventLoop\LoopInterface $loop) {
        return call_user_func(new Ratchet\Client\Factory($loop), $this->_url)
            ->then(function (Ratchet\Client\WebSocket $ws) {
                $ws->on('message', function ($msg) {
                    $data = $msg->getPayload();
                    /** @var BinaryNode $node */
                    $unpacked = Codec::decode($data);
                    list($type, $payload, $bonus) = $unpacked;
                    $this->invokeHandler($type, $payload, $bonus);
                });
            }, function ($e) {
                echo "Could not connect: {$e->getMessage()}\n";
            });
    }

    public function getClient() {
    }

    public function getNode() {
    }
}
