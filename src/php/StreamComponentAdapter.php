<?php


use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class StreamComponentAdapter implements StreamComponentInterface, BinaryComponentInterface {
    use BinaryHandlers;

    /**
     * @var StreamComponentInterface
     */
    private $_component;

    /**
     * @var ConnectionInterface
     */
    private $_current;

    public function __construct(StreamComponentInterface $component) {
        $this->_component = $component;
    }

    function onOpen(ConnectionInterface $conn) {
        $this->_current = $conn;
        $this->_component->onOpen($conn);
        $this->_current = null;
    }

    function onClose(ConnectionInterface $conn) {
        $this->_current = $conn;
        $this->_component->onClose($conn);
        $this->_current = null;
    }

    function onError(ConnectionInterface $conn, \Exception $e) {
        $this->_current = $conn;
        $this->_component->onError($conn, $e);
        $this->_current = null;
    }

    function onMessage(ConnectionInterface $from, $msg) {
        $this->_current = $from;
        $this->_component->onMessage($from, $msg);
        $this->_current = null;
    }

    function onStream(BinaryStream $stream, $meta) {
        $this->_component->onStream($stream, $meta);
    }

    function onBinaryMessage(ConnectionInterface $from, $msg) {
        $this->_current = $from;
        try {
            $unpacked = Codec::decode($msg);
            list($type, $payload, $bonus) = $unpacked;
            $this->invokeHandler($type, $payload, $bonus);
        } catch (\Exception $e) {
            $this->onError($from, $e);
        }
        $this->_current = null;
    }


    protected function getClient() {
        return $this->_current;
    }
}
