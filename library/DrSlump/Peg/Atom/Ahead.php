<?php

namespace DrSlump\Peg\Atom;

use DrSlump\Peg\Atom;
use DrSlump\Peg\Source\SourceInterface;
use DrSlump\Peg\Packrat\PackratInterface;
use DrSlump\Peg\Failure;


class Ahead extends Atom
{
    protected $atom;
    protected $expected;

    public function __construct(Atom $atom, $expected = true)
    {
        $this->atom = $atom;
        $this->expected = $expected;
    }

    protected function match(SourceInterface $source, PackratInterface $packrat)
    {
        $ofs = $source->tell();

        $result = $this->atom->apply($source, $packrat);

        // Always rollback the position since we don't want to consume it
        $source->seek($ofs);

        if ($result instanceof Failure && TRUE === $this->expected) {
            return $this->fail("lookahead: $this->atom didn't match but should have", $result);
        } else if (!($result instanceof Failure) && FALSE === $this->expected) {
            return $this->fail("negative lookahead: $this->atom matched but shouldn't have", $result);
        }

        // Ahead atoms do not generate parse nodes
        return NULL;
    }


    public function inspect($prefix = '')
    {
        if ($this->expected) {
            $s = $prefix . "Followed by:\n";
        } else {
            $s = $prefix . "Not followed by:\n";
        }

        $art = \DrSlump\Peg::$charArt;

        $prefix = str_replace($art[0], $art[2], $prefix);
        $prefix = str_replace($art[1], $art[3], $prefix);

        return $s . $this->atom->inspect($prefix . $art[1]);
    }


    public function __toString()
    {
        return ($this->expected ? '&' : '!') . $this->atom;
    }

}
