<?php


namespace arls\binarystream;


use Evenement\EventEmitterTrait;
use Hoa;
use MessagePack\Packer;
use Sabre\VObject\Property\Binary;

class BinaryStream {
    use EventEmitterTrait;

    const PAYLOAD_RESERVED = 0;
    const PAYLOAD_NEW_STREAM = 1;
    const PAYLOAD_DATA = 2;
    const PAYLOAD_PAUSE = 3;
    const PAYLOAD_RESUME = 4;
    const PAYLOAD_END = 5;
    const PAYLOAD_CLOSE = 6;

    private $_readable = true;
    private $_writable = true;
    private $_paused = false;

    private $_closed = false;
    private $_ended = false;

    private $_streamId;

    private $_node;
    private $_client;
    private $_meta;

    public function __construct($client, $streamId, $node = null, $options = []) {
        $this->_node = $node;
        $this->_streamId = $streamId;
        $this->_client = $client;

        /** @var $create */
        $meta = [];
        $create = false;
        extract($options, EXTR_OVERWRITE);
        $this->_meta = $meta;
        if ($create) {
            $this->_write(BinaryStream::PAYLOAD_NEW_STREAM, $meta, $streamId);
        }
    }

    public function onDrain() {
        if (!$this->_paused) {
            $this->emit('drain');
        }
    }

    public function onClose() {
        if ($this->_closed) {
            return;
        }
        $this->_readable = false;
        $this->_writable = false;
        $this->_closed = true;
        $this->emit('close');
    }

    public function onError($error) {
        $this->_readable = false;
        $this->_writable = false;
        $this->emit('error', [$error]);
    }

    public function getId() {
        return $this->_streamId;
    }

    public function isReadable() {
        return $this->_readable;
    }

    public function isWritable() {
        return $this->_readable;
    }

    // =======================================================
    // Write Stream
    // =======================================================

    public function onPause() {
        $this->_paused = true;
        $this->emit('pause');
    }

    public function onResume() {
        $this->_paused = false;
        $this->emit('resume');
        $this->emit('drain');
    }

    protected function _write($code, $data = null, $bonus = null) {
        if (!$this->_writable) {
            return false;
        }
        $message = self::getPacker()->packArray([$code, $data, $bonus]);
        $this->_client->send($message, $this->_node, Hoa\Websocket\Connection::OPCODE_BINARY_FRAME);
        return true;
    }

    public function write($data) {
        if ($this->_writable) {
            $out = $this->_write(self::PAYLOAD_DATA, $data, $this->_streamId);
            return !$this->_paused && $out;
        } else {
            $this->emit('error', ['stream is not writeable']);
        }
    }

    public function end() {
        $this->_ended = true;
        $this->_readable = false;
        $this->_write(BinaryStream::PAYLOAD_END, null, $this->_streamId);
    }

    public function close() {
        $this->onClose();
        $this->_write(BinaryStream::PAYLOAD_CLOSE, null, $this->_streamId);
    }

    // =======================================================
    // Read Stream
    // =======================================================

    public function onEnd() {
        if ($this->_ended) {
            return;
        }
        $this->_ended = true;
        $this->_readable = false;
        $this->emit('end');
    }

    public function onData($data) {
        $this->emit('data', [$data]);
    }

    public function pause() {
        $this->onPause();
        $this->_write(BinaryStream::PAYLOAD_PAUSE, null, $this->_streamId);
    }

    public function pipe($dest, $options = []) {
        return StreamHelpers::pipe($this, $dest, $options);
    }

    public function writeToFile($filename) {
        return StreamHelpers::writeToFile($this, $filename);
    }

    private static $_packer;

    /**
     * @return Packer
     */
    private static function getPacker() {
        if (self::$_packer === null) {
            self::$_packer = new Packer();
        }
        return self::$_packer;
    }
}

