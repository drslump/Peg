<?php

namespace DrSlump\SimpleParser\Atom;

use DrSlump\SimpleParser\Atom;
use DrSlump\SimpleParser\Node;
use DrSlump\SimpleParser\Source;


class RegExp extends Atom
{
    protected $pattern;

    public function __construct($pattern, $caseInsensitive = false)
    {
        $delims = array('/','@','#','!','%','&','=','~','`','"');
        foreach($delims as $delim) {
            if (FALSE === strpos($pattern, $delim)) {
                $this->pattern = $delim . $pattern . $delim . 'x';
                break;
            }
        }

        if (NULL === $this->pattern) {
            throw new \InvalidArgumentException('Regular expression cannot be enclosed in delimiters');
        }

        if ($caseInsensitive) {
            $this->pattern .= 'i';
        }
    }

    protected function match(Source $source)
    {
        $value = $source->read(1);

        echo "RegExp: '$value' against '$this->pattern'\n";

        if (!preg_match($this->pattern, $value)) {
            return false;
        }

        $node = new Node\Terminal($this->description ?: 'RegExp');
        $node->setValue($value);
        return $node;
    }
}
