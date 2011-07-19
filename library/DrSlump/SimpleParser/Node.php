<?php

namespace DrSlump\SimpleParser;

abstract class Node
{
    protected $name;

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

    abstract public function isRoot();
    abstract public function isTerminal();
    abstract public function isEmpty();
    abstract public function inspect($indent = 0);

}
