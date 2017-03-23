<?php


namespace arls\binarystream;

use Evenement\EventEmitterTrait;
use Hoa;

class BinaryServer {
    use EventEmitterTrait;

    /**
     * @var Hoa\Websocket\Server
     */
    private $_server;

    public function __construct(Hoa\Websocket\Server $server) {
        $this->_server = $server;
        $this->init();
    }

    protected function init() {
        $this->_server->getConnection()->setNodeName(BinaryNode::class);

        $this->_server->on('error', function (Hoa\Event\Bucket $bucket) {
            $node = $bucket->getSource()->getConnection()->getCurrentNode();
            foreach ($node->getStreams() as $stream) {
                $stream->onError($bucket->getData()['exception']);
            }
            $this->emit('error', [$bucket->getData()['exception']]);
        });
        $this->_server->on('close', function (Hoa\Event\Bucket $bucket) {
            /** @var BinaryNode $node */
            $node = $bucket->getSource()->getConnection()->getCurrentNode();
            foreach ($node->getStreams() as $stream) {
                /** @var BinaryStream */
                $stream->onClose();
            }
            $this->emit('close');
        });
        $this->_server->on('binary-message', function (Hoa\Event\Bucket $bucket) {
            /** @var BinaryNode $node */
            $node = $bucket->getSource()->getConnection()->getCurrentNode();
            $data = $bucket->getData()['message'];
            $unpacked = Codec::decode($data);
            list($type, $payload, $bonus) = $unpacked;
            $node->invokeHandler($type, $payload, $bonus);
        });
        $this->_server->on('open', function (Hoa\Event\Bucket $bucket) {
            $node = $bucket->getSource()->getConnection()->getCurrentNode();
            $node->client = $bucket->getSource();
            $this->emit('connection', [$node]);
        });
    }

    public function run() {
        $this->_server->run();
    }

    public function close($code, $message) {
        $this->_server->close($code, $message);
    }
}
