<?php


namespace arls\binarystream;


use Hoa;

class BinaryNode extends Hoa\Websocket\Node {
    use BinaryHandlers;

    public $client;

    public function getClient() {
        return $this->client;
    }

    public function getNode() {
        return $this;
    }
}
