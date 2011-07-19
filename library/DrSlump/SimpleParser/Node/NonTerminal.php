<?php

namespace DrSlump\SimpleParser\Node;

class NonTerminal extends Child
{
    /** @var \SplDoublyLinkedList */
    protected $elements;

    public function __construct($name)
    {
        parent::__construct($name);

        $this->elements = new \SplDoublyLinkedList();
    }

    public function isTerminal()
    {
        return false;
    }

    public function isEmpty()
    {
        return $this->elements->isEmpty();
    }

    public function inspect($indent = 0)
    {
        $s = str_repeat('  ', $indent);
        $s .= ($this->name ?: get_class($this)) . PHP_EOL;

        foreach ($this->elements as $child) {
            $s .= $child->inspect($indent+1) . PHP_EOL;
        }

        return $s;
    }


    public function appendChild(Child $node)
    {
        $this->elements->push($node);
    }

}
