<?php


namespace arleigh\wsstream;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

interface StreamComponentInterface extends MessageComponentInterface {
    function onStream(ConnectionInterface $connection, BinaryStream $stream, array $meta);
}
