<?php

namespace DrSlump\Peg\Atom;

use DrSlump\Peg\Atom;
use DrSlump\Peg\Node;
use DrSlump\Peg\Source\SourceInterface;
use DrSlump\Peg\Packrat\PackratInterface;
use DrSlump\Peg\Failure;


class RegExp extends Atom
{
    protected $pattern;
    protected $caseInsensitive = false;

    public function __construct($pattern, $caseInsensitive = false)
    {
        // Find a regexp delimiter from a character not found in the pattern
        $delims = array('/','@','#','!','%','&','=','~','`','"',"'",':',';','<','>','{','}','_');
        foreach($delims as $delim) {
            if (FALSE === strpos($pattern, $delim)) {
                $this->pattern = $delim . $pattern . $delim . 'SAsx';
                break;
            }
        }

        if (NULL === $this->pattern) {
            throw new \InvalidArgumentException('Regular expression cannot be enclosed in delimiters');
        }

        $this->caseInsensitive = $caseInsensitive;
    }


    protected function match(SourceInterface $source, PackratInterface $packrat = NULL)
    {
        $match = $source->match($this->pattern, $this->caseInsensitive);
        if (FALSE === $match) {
            return $this->fail("$this did not match");
        }

        return $match;
    }


    public function inspect($prefix = '')
    {
        return $prefix . $this;
    }

    public function __toString()
    {
        $s = preg_replace('/[a-z]+$/i', '', $this->pattern);
        if ($this->caseInsensitive) $s .= 'i';
        return $s;
    }
}
