<?php

use phpseclib\Crypt\Base;
use React\Stream\ThroughStream;

class CipherTransform extends ThroughStream {
    const OP_ENCRYPT = 'encrypt';
    const OP_DECRYPT = 'decrypt';

    private $_cipher;
    private $_op;

    public function __construct(Base $cipher, $op) {
        $this->_cipher = $cipher;
        $this->_op = $op;
        parent::__construct();
    }

    private $_buf;

    public function filter($data) {
        $this->_buf .= $data;
        $out = '';
        while (mb_strlen($this->_buf, '8bit') >= $this->getBlockSize()) {
            $out .= $this->_cipher->{$this->_op}(mb_substr($this->_buf, 0, $this->getBlockSize(), '8bit'));
            $this->_buf = mb_substr($this->_buf, $this->getBlockSize());
        }
        return $out;
    }


    public function end($data = null) {
        $this->write($data);
        $len = mb_strlen($this->_buf, '8bit');
        $buf = $this->_buf;
        $this->_buf = '';
        parent::end($buf . str_repeat("\0", $this->getBlockSize() - $len));
    }

    public function getBlockSize() {
        return $this->_cipher->block_size;
    }
}

