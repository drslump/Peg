<?php

namespace DrSlump\SimpleParser;

class Source
{
    protected $data;
    protected $offset = 0;
    protected $length = 0;

    public function __construct($data)
    {
        $this->data = $data;
        $this->length = strlen($data);
    }

    public function read($n)
    {
        $value = substr($this->data, $this->offset, $n);
        $this->offset += strlen($value);
        return $value;
    }

    public function eof()
    {
        return $this->offset >= $this->length;
    }

    public function pos()
    {
        return $this->offset;
    }

    public function seek($pos)
    {
        $this->offset = $pos;
    }
}
