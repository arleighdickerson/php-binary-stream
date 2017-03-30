<?php

use Ratchet\MessageComponentInterface;

interface StreamComponentInterface extends MessageComponentInterface {
    function onStream(BinaryStream $stream, $meta);
}