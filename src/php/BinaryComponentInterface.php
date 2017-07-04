<?php

namespace Arleigh\Ratchet\Stream;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

interface BinaryComponentInterface extends MessageComponentInterface {
    function onBinaryMessage(ConnectionInterface $from, $msg);
}
