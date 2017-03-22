<?hh

class Client {
    use BinaryHandlers;

    public function run() {
        Ratchet\Client\connect(
            "ws://"
            . Module::getInstance()->serverAddress
            . ":"
            . Module::getInstance()->wsPort
        )->then(function (Ratchet\Client\WebSocket $ws) {
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

	public function onOpen(Ratchet\Client\WebSocket $ws){
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

