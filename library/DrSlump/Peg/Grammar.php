<?php

namespace DrSlump\Peg;

use DrSlump\Peg;
use DrSlump\Peg\Atom;
use DrSlump\Peg\Source;

abstract class Grammar extends Atom
    implements \ArrayAccess
{
    /** @var string The initial rule for the grammar */
    protected $root = 'root';

    /** @var \DrSlump\Peg\Atom[] */
    protected $rules = array();


    public function __construct()
    {
        // Register this grammar as the active one
        Peg::pushGrammar($this);

        // Configure rules for this grammar
        $this->rules();

        // Unregister this grammar
        Peg::popGrammar();
    }

    // Implement this method to setup your grammar
    abstract protected function rules();


    public function root($name)
    {
        $this->root = $name;
    }

    public function parse($source)
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
        $node = $root->parse($source);


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
}
