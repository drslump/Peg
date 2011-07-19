<?php

namespace DrSlump\SimpleParser\Atom;

use DrSlump\SimpleParser\Atom;
use DrSlump\SimpleParser\Node;
use DrSlump\SimpleParser\Source;


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

    protected function match(Source $source)
    {
        $node = new Node\NonTerminal($this->description ?: 'Repeat{' . $this->min . ':' . $this->max . '}');

        $cnt = 0;
        while (NULL === $this->max || $cnt < $this->max) {

            $result = $this->atom->apply($source);
            if (FALSE === $result) {
                break;
            }

            $node->appendChild($result);

            $cnt++;
        }

        // Check if repetitions are within limits
        if ($cnt < $this->min || (NULL !== $this->max && $cnt > $this->max)) {
            return FALSE;
        }

        return $node;
    }
}
