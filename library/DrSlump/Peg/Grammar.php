<?php

namespace DrSlump\Peg;

use DrSlump\Peg;
use DrSlump\Peg\Atom;
use DrSlump\Peg\Source;
use DrSlump\Peg\Packrat\PackratInterface;

class Grammar extends Atom
    implements \ArrayAccess
{
    /** @var string The initial rule for the grammar */
    protected $root = 'root';

    /** @var \DrSlump\Peg\Atom[] */
    protected $rules = array();


    public function __construct($callback = NULL)
    {
        // Register this grammar as the active one
        Peg::pushGrammar($this);

        // Configure rules for this grammar
        if (NULL === $callback) {
            $this->rules();
        } else {
            call_user_func($callback, $this);
        }

        // Unregister this grammar
        Peg::popGrammar();
    }

    // Override this method to setup your grammar
    protected function rules()
    {
    }


    public function root($name)
    {
        $this->root = $name;
    }

    public function parse($source, PackratInterface $packrat = NULL)
    {
        $root = $this->root;

        if (!isset($this->rules[$root])) {
            throw new \RuntimeException(
                'Grammar root rule "' . $root . '" was not found in the ' .
                'available rules: ' . implode(', ', array_keys($this->rules))
            );
        }

        // Delegate the actual parsing to the root rule
        $root = $this->rules[$root];
        $node = $root->parse($source, $packrat);

        return $node;
    }



    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset An offset to check for.
     * @return boolean Returns true on success or false on failure.
     */
    public function offsetExists($offset)
    {
        return isset($this->rules[$offset]);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset The offset to retrieve.
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        // If it does not yet exists create an empty sequence
        if (!isset($this->rules[$offset])) {
            $this->rules[$offset] = new Atom\Sequence();
        }

        return $this->rules[$offset];
    }

    /**
     * Offset to set. This is special since it creates a new rule object
     * and automatically assigns an atom to it.
     *
     * @example
     *
     *     $grammar['space'] = rex('\s');
     *     $grammar['ident'] = rex('[a-z]')->once->ref('space')->maybe;
     *
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value The value to set.
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $value = Peg::argument($value);
        $this->rules[$offset] = $value;
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset  The offset to unset.
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->rules[$offset]);
    }


    public function inspect($prefix = '')
    {
        $objects = new \SplObjectStorage();

        $art = Peg::$charArt;

        $s = $prefix . "Grammar with the following rules:\n";

        $prefix = str_replace($art[0], $art[2], $prefix);
        $prefix = str_replace($art[1], $art[3], $prefix);

        $idx = 0;
        foreach ($this->rules as $name=>$atom) {
            if ($objects->contains($atom)) {
                $atom = new Atom\Reference($this, $objects[$atom]);
            }

            if ($idx === count($this->rules)-1) {
                $s .= $prefix . $art[1] . strtoupper($name) . ":\n";
                $s .= $atom->inspect($prefix . $art[3] . $art[1]) . "\n";
            } else {
                $s .= $prefix . $art[0] . strtoupper($name) . ":\n";
                $s .= $atom->inspect($prefix . $art[2] . $art[1]) . "\n";
            }


            $objects->attach($atom, $name);

            $idx++;
        }

        return rtrim($s);
    }
}
