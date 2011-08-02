<?php

namespace DrSlump\Peg;

class Node
{
    protected $name;
    protected $value;

    public function __construct($name = NULL)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    //abstract public function isRoot();
    //abstract public function isTerminal();
    //abstract public function isEmpty();
    //abstract public function inspect($indent = 0);

}
