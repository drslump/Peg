<?php

namespace DrSlump\Peg\Atom;

use DrSlump\Peg\Atom;
use DrSlump\Peg\Node;
use DrSlump\Peg\Source\SourceInterface;
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

    protected function match(SourceInterface $source)
    {
        $named = array();
        $strings = array();
        
        $cnt = 0;
        while (NULL === $this->max || $cnt < $this->max) {

            $result = $this->atom->apply($source);
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
        if ($cnt < $this->min || (NULL !== $this->max && $cnt > $this->max)) {
            return new Failure('Not enough repetitions');
        }

        // If there are named results just return them
        if (count($named)) {
            return $named;
        }

        // Otherwise return a single string
        return implode('', $strings);
    }
}
