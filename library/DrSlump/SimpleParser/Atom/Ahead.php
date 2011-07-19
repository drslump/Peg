<?php

namespace DrSlump\SimpleParser\Atom;

use DrSlump\SimpleParser\Atom;
use DrSlump\SimpleParser\Source;


class Ahead extends Atom
{
    protected $atom;
    protected $expected;

    public function __construct(Atom $atom, $expected = true)
    {
        $this->atom = $atom;
        $this->expected = $expected;
    }

    protected function match(Source $source)
    {
        $ofs = $source->pos();

        $result = $this->atom->apply($source);

        if (FALSE === $result && TRUE === $this->expected) {
            return FALSE;
        } else if (FALSE !== $result && FALSE === $this->expected) {
            return FALSE;
        }

        // Rollback the position since we don't want to consume it
        $source->seek($ofs);
        // Ahead atoms do not generate parse nodes
        return NULL;
    }
}
