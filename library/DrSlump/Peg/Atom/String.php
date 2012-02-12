<?php

namespace DrSlump\Peg\Atom;

use DrSlump\Peg\Atom;
use DrSlump\Peg\Node;
use DrSlump\Peg\Source\SourceInterface;
use DrSlump\Peg\Packrat\PackratInterface;
use DrSlump\Peg\Failure;


class String extends Atom
{
    static $printable = array(
        "\t" => '\t',
        "\r" => '\r',
        "\n" => '\n',
    );

    protected $expected = '';
    protected $caseInsensitive = FALSE;

    public function __construct($expected, $caseInsensitive = false)
    {
        $this->expected = $expected;
        $this->caseInsensitive = $caseInsensitive;
    }

    protected function match(SourceInterface $source, PackratInterface $packrat = NULL)
    {

        $value = $source->compare($this->expected, $this->caseInsensitive);
        if (FALSE === $value) {
            return $this->fail("Expected '$this->expected'");
        }

        return $value;
    }

    public function inspect($prefix = '')
    {
        return $prefix . $this;
    }

    public function __toString()
    {
        // Make somewhat safer to print
        $expected = addcslashes($this->expected, "\0..\37!@\177..\377");

        return $this->caseInsensitive
               ? "'" . $expected . "'"
               : '"' . $expected . '"';
    }
}
