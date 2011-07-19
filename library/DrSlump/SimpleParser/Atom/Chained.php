<?php

namespace DrSlump\SimpleParser\Atom;

use DrSlump\SimpleParser\Atom;
use DrSlump\SimpleParser\Node;
use DrSlump\SimpleParser\Source;


/**
 * This class is only used to tell apart explicit sequences from the ones
 * generated when chaining methods.
 *
 * It overrides the look-ahead and repetition modifiers so that they work
 * over the last atom in the chained sequence and not the sequence as a whole.
 *
 *   $this['rule'] = ref('foo') -> str(':')->repeat -> ref('bar')
 *
 */
class Chained extends Atom\Sequence
{
    /**
     * Flags the atom as repeated
     *
     * @param int $min
     * @param int|null $max
     * @return Atom\Repeat
     */
    public function repeat($min = 0, $max = NULL)
    {
        $idx = count($this->atoms) - 1;
        $this->atoms[$idx] = $this->atoms[$idx]->repeat($min, $max);
        return $this;
    }

    /**
     * Positive look ahead
     *
     * @param string|null $value
     * @return Atom\Ahead|Atom\Sequence
     */
    public function has($value = NULL)
    {
        if (NULL !== $value) {
            return parent::has($value);
        }

        $idx = count($this->atoms) - 1;
        $this->atoms[$idx] = $this->atoms[$idx]->has();
        return $this;
    }

    /**
     * Negative look ahead
     *
     * @param string|null $value
     * @return Atom\Ahead|Atom\Sequence
     */
    public function not($value = NULL)
    {
        if (NULL !== $value) {
            return parent::not($value);
        }

        $idx = count($this->atoms) - 1;
        $this->atoms[$idx] = $this->atoms[$idx]->not();
        return $this;
    }
}
