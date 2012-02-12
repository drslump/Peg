<?php

namespace DrSlump\Peg;

class Failure
{
    /** @var mixed */
    protected $reason;
    /** @var \DrSlump\Peg\Source\SourceInterface */
    protected $source;
    /** @var int */
    protected $row;
    /** @var int */
    protected $column;
    /** @var \DrSlump\Peg\Atom */
    protected $atom;
    /** @var \DrSlump\Peg\Failure[] */
    protected $children = array();


    public function __construct($reason)
    {
        $this->reason = $reason;
    }

    public function setSource($source)
    {
        $this->source = $source;
        $this->row = $source->row();
        $this->column = $source->column();
    }

    public function getSource()
    {
        return $this->source;
    }

    public function setAtom($atom)
    {
        $this->atom = $atom;
    }

    public function getAtom()
    {
        return $this->atom;
    }

    public function setReason($reason)
    {
        $this->reason = $reason;
    }

    public function getReason()
    {
        return $this->reason;
    }

    public function setChildren($children)
    {
        $this->children = is_array($children) ? $children : array($children);
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function inspect($prefix = '')
    {
        $art = \DrSlump\Peg::$charArt;

        $s = $prefix . $this->getReason() . " at " .
             $this->row . ":" . $this->column . "\n";

        $prefix = str_replace($art[0], $art[2], $prefix);
        $prefix = str_replace($art[1], $art[3], $prefix);
        foreach ($this->children as $idx=>$child) {
            if ($idx === count($this->children)-1) {
                $s .= $child->inspect($prefix . $art[1]) . "\n";
            } else {
                $s .= $child->inspect($prefix . $art[0]) . "\n";
            }
        }

        return rtrim($s);
    }

    public function __toString()
    {
        return $this->inspect();
    }

}
