<?php

namespace DrSlump\Peg\Node;

class Terminal extends Child
{
    // The substring of the input represented by this node.
    protected $value = '';


    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue($value)
    {
        return $this->value;
    }

    public function isTerminal()
    {
        return true;
    }

    public function isEmpty()
    {
        return 0 === strlen($this->value);
    }

    public function inspect($indent = 0)
    {
        $s = str_repeat('  ', $indent);
        $s .= ($this->name ?: get_class($this)) . ': ';
        return $s . '"' . $this->value . '"';
    }

}