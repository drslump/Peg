<?php

namespace DrSlump\SimpleParser\Atom;

use DrSlump\SimpleParser\Atom;
use DrSlump\SimpleParser\Node;
use DrSlump\SimpleParser\Source;


class Sequence extends Atom
{
    /** \DrSlump\SimpleParser\Atom[] */
    protected $atoms;

    /**
     * @param \DrSlump\SimpleParser\Atom[] $atoms
     */
    public function __construct(array $atoms = array())
    {
        $this->atoms = $atoms;
    }

    public function add(Atom $atom)
    {
        $this->atoms[] = $atom;
    }

    protected function match(Source $source)
    {
        $node = new Node\NonTerminal($this->description ?: 'Sequence');

        foreach ($this->atoms as $atom) {
            $result = $atom->apply($source);
            if (FALSE === $result) {
                return FALSE;
            }

            if (NULL !== $result) {
                $node->appendChild($result);
            }
        }

        return $node;
    }
}
