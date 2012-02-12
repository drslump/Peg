<?php

namespace DrSlump\Peg\Atom;

use DrSlump\Peg\Atom;
use DrSlump\Peg\Node;
use DrSlump\Peg\Source\SourceInterface;
use DrSlump\Peg\Packrat\PackratInterface;
use DrSlump\Peg\Failure;


class Sequence extends Atom
{
    /** \DrSlump\Peg\Atom[] */
    protected $atoms;

    /**
     * @param \DrSlump\Peg\Atom[] $atoms
     */
    public function __construct(array $atoms = array())
    {
        $this->atoms = $atoms;
    }

    public function add(Atom $atom)
    {
        $this->atoms[] = $atom;
    }

    protected function match(SourceInterface $source, PackratInterface $packrat = NULL)
    {
        $strings = array();
        $named = array();
        foreach ($this->atoms as $atom) {

            $result = $atom->apply($source, $packrat);

            if (NULL === $result) {
                continue;
            } else if ($result instanceof Failure) {
                return $this->fail("Failed to match sequence $this", $result);
            } else if ($result instanceof Node) {
                $named[] = $result;
            } else if (is_array($result)) {
                $named = array_merge($named, $result);
            } else {
                $strings[] = $result;
            }
        }

        // If there are named results just return them
        if (count($named)) {
            return $named;
        }

        // Otherwise return a single string
        return implode('', $strings);
    }



    protected function DEFER_match(SourceInterface $source, PackratInterface $packrat = NULL)
    {
        $atoms = $this->atoms;
        $strings = array();
        $named = array();
        $idx = 0;

        if (empty($atoms)) { 
            throw \RuntimeException("Empty sequence");
        }

        $cont = function($result) use (&$cont, &$idx, &$atoms, &$strings, &$named) {

            // TODO: Shouldn't we call ->apply from here?

            if ($result instanceof Failure) {
                return $this->fail("Failed to match sequence $this", $result);
            } else if ($result instanceof Node) {
                $named[] = $result;
            } else if (is_array($result)) {
                $named = array_merge($named, $result);
            } else if ($result) {
                $strings[] = $result;
            }

            $idx++;
            if ($idx < count($atoms)) {
                return Continuation($atoms[$idx], $cont);
            } else {
                // If there are named results just return them
                if (count($named)) {
                    return $named;
                }

                // Otherwise return a single string
                return implode('', $string);
            }
        };

        return Continuation($this->atoms[$idx], $cont); 
    }



    public function inspect($prefix = '')
    {
        $art = \DrSlump\Peg::$charArt;

        $s = $prefix . "A sequence of:\n";

        $prefix = str_replace($art[0], $art[2], $prefix);
        $prefix = str_replace($art[1], $art[3], $prefix);
        foreach ($this->atoms as $idx=>$atom) {
            if ($idx === count($this->atoms)-1) {
                $s .= $atom->inspect($prefix . $art[1]) . "\n";
            } else {
                $s .= $atom->inspect($prefix . $art[0]) . "\n";
            }
        }

        return rtrim($s);
    }

    public function __toString()
    {
        return '(' . implode(' -> ', $this->atoms) . ')';
    }
}
