<?php

namespace DrSlump\SimpleParser\Atom;

use DrSlump\SimpleParser\Atom;
use DrSlump\SimpleParser\Source;

class Named extends Atom
{
    /** @var \DrSlump\SimpleParser\Atom */
    protected $atom;
    /** @var string */
    protected $name;

    public function __construct(Atom $atom, $name)
    {
        $this->atom = $atom;
        $this->name = $name;
    }

    public function apply(Source $source)
    {
        $result = $this->atom->apply($source);
        return $result;
    }
}
