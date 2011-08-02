<?php

namespace DrSlump\Peg\Atom;

use DrSlump\Peg\Grammar;
use DrSlump\Peg\Atom;
use DrSlump\Peg\Node;
use DrSlump\Peg\Source\SourceInterface;


class Reference extends Atom
{
    /** @var \DrSlump\Peg\Grammar */
    protected $grammar;
    /** @var string */
    protected $reference;

    public function __construct(Grammar $grammar, $reference)
    {
        $this->grammar = $grammar;
        $this->reference = $reference;
    }

    protected function match(SourceInterface $source)
    {
        echo "REFERENCE: $this->reference\n";

        // Fetch the referenced rule and just proxy it
        $rule = $this->grammar[$this->reference];
        return $rule->apply($source);
    }
}
