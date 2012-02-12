<?php

namespace DrSlump\Peg\Atom;

use DrSlump\Peg\Atom;
use DrSlump\Peg\Source\SourceInterface;
use DrSlump\Peg\Packrat\PackratInterface;
use DrSlump\Peg\Failure;
use DrSlump\Peg\Node;


class Named extends Atom
{
    /** @var \DrSlump\Peg\Atom */
    protected $atom;
    /** @var string */
    protected $name;

    public function __construct(Atom $atom, $name)
    {
        $this->atom = $atom;
        $this->name = $name;
    }

    protected function match(SourceInterface $source, PackratInterface $packrat)
    {
        $row = $source->row();
        $col = $source->column();

        $result = $this->atom->apply($source, $packrat);

        if ($result instanceof Failure) {
            return $result;
        }

        // If successful create a node object
        $node = new Node($this->name, $row, $col);
        $node->setValue($result);

        return $node;
    }

    public function inspect($prefix = '')
    {
        return $prefix . $this;
    }

    public function __toString()
    {
        return $this->name . ':' . $this->atom;
    }
}
