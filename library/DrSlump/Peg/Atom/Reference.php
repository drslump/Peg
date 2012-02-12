<?php

namespace DrSlump\Peg\Atom;

use DrSlump\Peg\Grammar;
use DrSlump\Peg\Atom;
use DrSlump\Peg\Node;
use DrSlump\Peg\Source\SourceInterface;
use DrSlump\Peg\Packrat\PackratInterface;


class Reference extends Atom
{
    /** @var \DrSlump\Peg\Grammar */
    protected $grammar;
    /** @var string */
    protected $reference;

    /** @var int - Used to detect left-recursive patterns */
    protected $lastOffset = NULL;

    public function __construct(Grammar $grammar, $reference)
    {
        $this->grammar = $grammar;
        $this->reference = $reference;
    }

    protected function match(SourceInterface $source, PackratInterface $packrat = NULL)
    {
        $ofs = $source->tell();

        // TODO: Improve the exception
        if ($ofs === $this->lastOffset) {
            throw new \RuntimeException("Left recursion found with rule $this. Please modify your grammar.");
        }

        $this->lastOffset = $ofs;

        if (!isset($this->grammar[$this->reference])) {
            throw new \RuntimeException("Unknown rule $this->reference");
        }

        // Fetch the referenced rule and just proxy it
        $rule = $this->grammar[$this->reference];
        $result = $rule->apply($source, $packrat);

        $this->lastOffset = NULL;

        return $result;
    }


    public function inspect($prefix = '')
    {
        return $prefix . $this;
    }

    public function __toString()
    {
        return strtoupper($this->reference);
    }
}
