<?php

namespace DrSlump\Peg\Atom;

use DrSlump\Peg\Atom;
use DrSlump\Peg\Node;
use DrSlump\Peg\Source\SourceInterface;
use DrSlump\Peg\Packrat\PackratInterface;
use DrSlump\Peg\Failure;


class Repeat extends Atom
{
    protected $atom;
    protected $min;
    protected $max;

    public function __construct(Atom $atom, $min = 0, $max = NULL)
    {
        $this->atom = $atom;
        $this->min = $min;
        $this->max = $max;
    }

    protected function match(SourceInterface $source, PackratInterface $packrat = NULL)
    {
        $named = array();
        $strings = array();

        PEG_DEBUG and print("Repeat start: $this->atom\n");

        $cnt = 0;
        $result = NULL;
        while (NULL === $this->max || $cnt < $this->max) {

            $result = $this->atom->apply($source, $packrat);
            //echo "CNT: $cnt MAX: $this->max MIN: $this->min --<" . get_class($result) . ">-- \n";
            if ($result instanceof Failure) {
                break;
            }

            $cnt++;

            if (NULL === $result) {
                continue;
            }

            if (is_string($result)) {
                $strings[] = $result;
            } else {
                $named[] = $result;
            }
        }

        // Check if repetitions are within limits
        if ($cnt < $this->min) {
            return $this->fail("Expected at least {$this->min} of {$this->atom}", $result);
        }

        PEG_DEBUG and print("Repeat $cnt OK: $this->atom\n");

        // If there are named results just return them
        if (count($named)) {
            return $named;
        }

        // Otherwise return a single string
        return implode('', $strings);
    }


    public function inspect($prefix = '')
    {
        $art = \DrSlump\Peg::$charArt;

        if (NULL !== $this->max) {
            $s = $prefix . "Between $this->min and $this->max occurrences of:\n";
        } else {
            $s = $prefix . "At least $this->min occurrences of:\n";
        }

        $prefix = str_replace($art[0], $art[2], $prefix);
        $prefix = str_replace($art[1], $art[3], $prefix);

        return $s . $this->atom->inspect($prefix . $art[1]);
    }

    public function __toString()
    {
        if ($this->min === 0 && $this->max === 1) {
            $s = '?';
        } else if ($this->min === 0 && $this->max === NULL) {
            $s = '*';
        } else if ($this->min === 1 && $this->max === NULL) {
            $s = '+';
        } else {
            $s = '{' . $this->min . ',' . $this->max . '}';
        }

        return $this->atom . $s;
    }

}
