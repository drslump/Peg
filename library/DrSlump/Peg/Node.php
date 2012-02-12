<?php

namespace DrSlump\Peg;

class Node
{
    protected $name;
    protected $value;
    protected $row;
    protected $column;

    public function __construct($name, $row = NULL, $col = NULL)
    {
        $this->name = $name;
        $this->row = $row;
        $this->column = $col;
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

    public function getRow()
    {
        return $this->row;
    }

    public function getColumn()
    {
        return $this->column;
    }

}
