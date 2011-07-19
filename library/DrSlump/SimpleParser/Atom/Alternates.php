<?php

namespace DrSlump\SimpleParser\Atom;

use DrSlump\SimpleParser\Node;
use DrSlump\SimpleParser\Atom;
use DrSlump\SimpleParser\Source;


class Alternates extends Atom
{
    /** @var \DrSlump\SimpleParser\Atom[] */
    protected $atoms = array();

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
        foreach ($this->atoms as $atom) {
            $result = $atom->apply($source);
            if (FALSE !== $result) {
                echo "Alternate: OK!\n";
                if ($this->description) {
                    $result->setName($this->description);
                }
                return $result;
            }
        }

        echo "Alternate: Fail\n";

        return FALSE;
    }
}
