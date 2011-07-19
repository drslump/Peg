<?php

namespace DrSlump\SimpleParser\Atom;

use DrSlump\SimpleParser\Grammar;
use DrSlump\SimpleParser\Atom;
use DrSlump\SimpleParser\Node;
use DrSlump\SimpleParser\Source;


class Reference extends Atom
{
    /** @var \DrSlump\SimpleParser\Grammar */
    protected $grammar;
    /** @var string */
    protected $reference;

    public function __construct(Grammar $grammar, $reference)
    {
        $this->grammar = $grammar;
        $this->reference = $reference;
    }

    protected function match(Source $source)
    {
        echo "REFERENCE: $this->reference\n";

        $rule = $this->grammar[$this->reference];
        return $rule->apply($source);
    }
}
