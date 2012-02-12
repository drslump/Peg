<?php

namespace DrSlump\Peg\Atom;

use DrSlump\Peg\Node;
use DrSlump\Peg\Atom;
use DrSlump\Peg\Source\SourceInterface;
use DrSlump\Peg\Packrat\PackratInterface;
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

    protected function match(SourceInterface $source, PackratInterface $packrat = NULL)
    {
        $ofs = $source->tell();

        $failures = array();
        foreach ($this->atoms as $atom) {
            $result = $atom->apply($source, $packrat);
            if (!($result instanceof Failure)) {
                PEG_DEBUG and print("Alternate ($this): OK!\n");
                return $result;
            }

            $failures[] = $result;

            // Go back for another try
            $source->seek($ofs);
        }

        PEG_DEBUG and print("Alternate ($this): Fail\n");
        return $this->fail('Expected one of ' . $this, $failures);
    }

    public function inspect($prefix = '')
    {
        $art = \DrSlump\Peg::$charArt;

        $s = $prefix . "One of:\n";

        $prefix = str_replace($art[0], $art[2], $prefix);
        $prefix = str_replace($art[1], $art[3], $prefix);
        foreach ($this->atoms as $idx=>$atom) {
            if ($idx === count($this->atoms)-1) {
                $s .= $atom->inspect($prefix . $art[1]) . "\n";
            } else {
                $s .= $atom->inspect($prefix . $art[0]) . "\n";
            }
        }

        return rtrim($s);
    }

    public function __toString()
    {
        return implode(' | ', $this->atoms);
    }
}
