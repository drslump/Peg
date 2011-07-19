<?php

namespace DrSlump\SimpleParser\Atom;

use DrSlump\SimpleParser\Atom;
use DrSlump\SimpleParser\Node;
use DrSlump\SimpleParser\Source;


class String extends Atom
{
    protected $expected = '';
    protected $caseInsensitive = FALSE;

    public function __construct($expected, $caseInsensitive = false)
    {
        $this->expected = $expected;
        $this->caseInsensitive = $caseInsensitive;
    }

    protected function match(Source $source)
    {
        $value = $source->read(strlen($this->expected));

        echo "String: '$value' against '$this->expected'\n";

        if ($this->caseInsensitive && strcasecmp($value, $this->expected) !== 0) {
            return false;
        } else if (!$this->caseInsensitive && $value !== $this->expected) {
            return false;
        }

        $node = new Node\Terminal($this->description ?: 'String');
        $node->setValue($value);
        return $node;
    }
}
