<?php

namespace DrSlump\Peg\Atom;

use DrSlump\Peg\Atom;
use DrSlump\Peg\Source\SourceInterface;
use DrSlump\Peg\Packrat\PackratInterface;
use DrSlump\Peg\Failure;

/**
 * Allows to customize the errors generated when parsing the input.
 *
 * TODO: Improve this by making failures coming from this atom override
 *       all the other failures in the same branch of the error tree.
 */
class Error extends Atom
{
    /** @var \DrSlump\Peg\Atom */
    protected $atom;
    /** @var string */
    protected $message;

    public function __construct(Atom $atom, $message)
    {
        $this->atom = $atom;
        $this->message = $message;
    }

    protected function match(SourceInterface $source, PackratInterface $packrat = NULL)
    {
        $result = $this->atom->apply($source, $packrat);

        // If it actually fails we just forward the result
        if ($result instanceof Failure) {
            return $result;
        }

        // Create a failure with the configured message
        return $this->fail($this->message);
    }

    public function inspect($prefix = '')
    {
        return $prefix . $this;
    }

    public function __toString()
    {
        return 'Error(' . $this->message . '): ' . $this->atom;
    }
}
