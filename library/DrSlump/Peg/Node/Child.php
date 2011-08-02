<?php

namespace DrSlump\Peg\Node;

use DrSlump\Peg\Node;

abstract class Child extends Node
{
    /** @var \DrSlump\Peg\Node */
    protected $parent = null;

    // The position where this node was found in the source
    protected $offset = 0;


    public function isRoot()
    {
        return false;
    }

    public function setParent(Node $node)
    {
        $this->parent = $node;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getRoot()
    {
        $parent = $this;
        do {
            $parent = $parent->getParent();
        } while (!$parent->isRoot());

        return $parent;
    }

    public function setOffset($ofs)
    {
        $this->offset = $ofs;
    }

    public function getOffset($ofs)
    {
        return $this->offset;
    }

}
