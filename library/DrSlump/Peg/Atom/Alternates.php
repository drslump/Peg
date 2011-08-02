<?php

namespace DrSlump\Peg\Atom;

use DrSlump\Peg\Node;
use DrSlump\Peg\Atom;
use DrSlump\Peg\Source\SourceInterface;
use DrSlump\Peg\Failure;


class Alternates extends Atom
{
    /** @var \DrSlump\Peg\Atom[] */
    protected $atoms = array();

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
        $ofs = $source->tell();

        foreach ($this->atoms as $atom) {
            $result = $atom->apply($source);
            if (!($result instanceof Failure)) {
                echo "Alternate: OK!\n";
                return $result;
            }

            $source->seek($ofs);
        }

        echo "Alternate: Fail\n";
        return new Failure('None of the alternatives match');
    }
}
