<?php

namespace DrSlump\Peg\Atom;

use DrSlump\Peg\Atom;
use DrSlump\Peg\Node;
use DrSlump\Peg\Source\SourceInterface;
use DrSlump\Peg\Failure;


class String extends Atom
{
    protected $expected = '';
    protected $caseInsensitive = FALSE;

    public function __construct($expected, $caseInsensitive = false)
    {
        $this->expected = $expected;
        $this->caseInsensitive = $caseInsensitive;
    }

    protected function match(SourceInterface $source)
    {
        $value = $source->compare($this->expected, $this->caseInsensitive);

        if (FALSE === $value) {
            return new Failure("$this->expected was not found");
        }

        return $value;
    }
}
