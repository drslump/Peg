<?php

namespace DrSlump\Peg\Atom;

use DrSlump\Peg\Atom;
use DrSlump\Peg\Source\SourceInterface;
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

    protected function match(SourceInterface $source)
    {
        $ofs = $source->tell();

        $result = $this->atom->apply($source);

        // Always rollback the position since we don't want to consume it
        $source->seek($ofs);

        if ($result instanceof Failure && TRUE === $this->expected) {
            return new Failure('Expected to be followed');
        } else if (!($result instanceof Failure) && FALSE === $this->expected) {
            return new Failure('Expected not to be followed');
        }

        // Ahead atoms do not generate parse nodes
        return NULL;
    }
}
