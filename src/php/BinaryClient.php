<?php

namespace arls\binarystream;

class Client {
    use BinaryHandlers;

    private $_url;

    public function __construct($url) {
        $this->_url = $url;
    }

    public function run() {
        Ratchet\Client\connect($this->_url)->then(function (Ratchet\Client\WebSocket $ws) {
            $ws->on('message', function ($msg) {
                $data = $msg->getPayload();
                /** @var BinaryNode $node */
                $unpacked = $this->unpack($data);
                list($type, $payload, $bonus) = $unpacked;
                $this->invokeHandler($type, $payload, $bonus);
            });
            $this->onOpen($ws);
        }, function ($e) {
            echo "Could not connect: {$e->getMessage()}\n";
        });
    }

    public function onOpen(Ratchet\Client\WebSocket $ws) {
        //override THIS one
        return;
    }

    public function getClient() {
    }

    public function getNode() {
    }

    /**
     * @var Unpacker|null
     */
    private static $_unpacker;

    private static function unpack($value) {
        if (self::$_unpacker === null) {
            $bufferUnpacker = new BufferUnpacker();
            self::$_unpacker = new Unpacker($bufferUnpacker);
        }
        return self::$_unpacker->unpack($value);
    }
}
     */
    private static $_unpacker;

    private static function unpack($value) {
        if (self::$_unpacker === null) {
            $bufferUnpacker = new BufferUnpacker();
            self::$_unpacker = new Unpacker($bufferUnpacker);
        }
        return self::$_unpacker->unpack($value);
    }
}
