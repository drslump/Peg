<?php

namespace DrSlump\Peg\Atom;

use DrSlump\Peg\Atom;
use DrSlump\Peg\Node;
use DrSlump\Peg\Source\SourceInterface;
use DrSlump\Peg\Failure;


class Sequence extends Atom
{
    /** \DrSlump\Peg\Atom[] */
    protected $atoms;

    /**
     * @param \DrSlump\Peg\Atom[] $atoms
     */
    public function __construct(array $atoms = array())
    {
        $this->atoms = $atoms;
    }

    public function add(Atom $atom)
    {
        $this->atoms[] = $atom;
    }

    protected function match(SourceInterface $source)
    {
        $strings = array();
        $named = array();
        foreach ($this->atoms as $atom) {

            $result = $atom->apply($source);
            if ($result instanceof Failure) {
                return $result;
            }

            if (NULL === $result) {
                continue;
            }

            if ($result instanceof Node) {
                $named[] = $result;
            } else {
                $strings[] = $result;
            }
        }

        // If there are named results just return them
        if (count($named)) {
            return $named;
        }

        // Otherwise return a single string
        return implode('', $strings);
    }
}
