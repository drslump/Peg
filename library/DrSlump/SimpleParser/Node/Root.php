<?php

namespace DrSlump\SimpleParser\Node;

class Root extends NonTerminal
{
    // Source contents (do we actually need this here?)
    protected $source = '';


    public function isRoot()
    {
        return true;
    }

    public function setParent($node)
    {
        throw new \BadMethodCallException('Root nodes do not allow to be attached to a parent');
    }



}