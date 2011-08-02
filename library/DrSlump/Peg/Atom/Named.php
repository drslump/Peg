<?php

namespace DrSlump\Peg\Atom;

use DrSlump\Peg\Atom;
use DrSlump\Peg\Source\SourceInterface;
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

    protected function match(SourceInterface $source)
    {
        $result = $this->atom->apply($source);

        // If successful create a node object
        if ($result instanceof Failure) {
            return $result;
        }

        $node = new Node($this->name);
        $node->setValue($result);

        return $node;
    }
}
