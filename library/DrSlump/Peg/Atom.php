<?php

namespace DrSlump\Peg;

use \DrSlump\Peg;
use \DrSlump\Peg\Source;

/**
 * Class representing a grammar atom.
 *
 * Magic properties:
 *
 * @property Atom\Repeat $repeat     If present can be repeated
 * @property Atom\Repeat $rep        If present can be repeated
 * @property Atom\Repeat $many       If present can be repeated
 * @property Atom\Repeat $zeroOrMore If present can be repeated
 * @property Atom\Repeat $optional   Maybe present or not
 * @property Atom\Repeat $maybe      Maybe present or not
 * @property Atom\Repeat $zeroOrOne  At least once
 * @property Atom\Repeat $once       At least once
 * @property Atom\Repeat $many1      At least once
 * @property Atom\Repeat $oneOrMore  At least once
 *
 * @property Atom\Ahead $absent      Check it does not match but do not consume it
 * @property Atom\Ahead $present     Check it matches but do not consume it
 *
 * @property Atom $ignore            Do not use the matched text in the token
 *
 * @method Atom\Named as(string $name)  Apply a name to the atom
 */
abstract class Atom
{
    /** @var bool */
    public $ignored = false;

    
    /** @var \Closure */
    protected $cb;


    /**
     * Parses the given input. This is the method to use when you want to
     * process a source.
     *
     * @param string | \DrSlump\Peg\Source\SourceInterface $source
     * @return \DrSlump\Peg\Node
     */
    public function parse($source)
    {
        if (is_string($source)) {
            $source = new Source\Ascii($source);
        }

        $result = $this->apply($source);
        if ($result instanceof Failure) {
            throw new \Exception('Parser failed: ' . $result->getValue());
        }

        // Force a read to trigger the EOF
        if (!$source->eof() && FALSE !== $source->read(1)) {
            throw new \Exception('Parser terminated before end of file');
        }

        return $result;
    }

    /**
     * Calls the match() method of the atom. In case of an error, apply will
     * leave the source in the state it was before the match attempt.
     *
     * @param \DrSlump\Peg\Source\SourceInterface $source
     * @return mixed
     */
    public function apply(Source\SourceInterface $source)
    {
        $ofs = $source->tell();

        // Try the match
        $result = $this->match($source);
        if ($result instanceof Failure) {
            $source->seek($ofs);
            return $result;
        }

        // If we have custom logic for this match run it
        if (is_callable($this->cb)) {
            $result = call_user_func($this->cb, $result);
            if ($result instanceof Failure) {
                $source->tell($ofs);
                return $result;
            }
        }

        return $this->ignored ? NULL : $result;
    }

    /**
     * Checks if the atom constraints matches the source.
     *
     * @param \DrSlump\Peg\Source\SourceInterface $source
     * @return false | string
     */
    protected function match(Source\SourceInterface $source)
    {
        throw new \BadMethodCallException('Atom object should implement the match() method');
    }



    // Modifiers

    /**
     * Assigns a name to the atom
     *
     * @param string $name
     * @return Atom\Named
     */
    public function name($name)
    {
        return new Atom\Named($this, $name);
    }

    /**
     * Flags the atom as repeated
     *
     * @param int $min
     * @param int|null $max
     * @return Atom\Repeat
     */
    public function repeat($min = 0, $max = NULL)
    {
        return new Atom\Repeat($this, $min, $max);
    }

    /**
     * Flags the atom as optional (maybe)
     *
     * @return Atom\Repeat
     */
    public function optional()
    {
        return $this->repeat(0, 1);
    }

    /**
     * Register a callback to use to modify or reject a value
     *
     * @param \Closure $callback
     * @return Base
     */
    public function cb($callback)
    {
        $this->cb = $callback;
        return $this;
    }


    // Sequence

    /**
     * Positive look ahead
     *
     * @param string|null $value
     * @return Atom\Ahead|Atom\Sequence
     */
    public function has($value = NULL)
    {
        if (NULL !== $value) {
            $value = Peg::argument($value);
            $atom = new Atom\Ahead($value, true);
            return $this->_sequence($atom);
        } else {
            return new Atom\Ahead($this, true);
        }
    }

    /**
     * Negative look ahead
     *
     * @param string|null $value
     * @return Atom\Ahead|Atom\Sequence
     */
    public function not($value = NULL)
    {
        if (NULL !== $value) {
            $value = Peg::argument($value);
            $atom = new Atom\Ahead($value, false);
            return $this->_sequence($atom);
        } else {
            return new Atom\Ahead($this, false);
        }
    }

    /**
     * Helper method to create a sequence if it doesn't yet exists
     *
     * @param Atom $atom
     * @return Atom\Sequence
     */
    protected function _sequence(Atom $atom)
    {
        if ($this instanceof Atom\Chained) {
            $this->add($atom);
            return $this;
        }

        return new Atom\Chained(array($this, $atom));
    }

    /**
     * @param string $value
     * @return Atom\Sequence
     */
    public function str($value)
    {
        return $this->_sequence(
            Peg::str($value)
        );
    }

    /**
     * @param string $value
     * @return Atom\Sequence
     */
    public function stri($value)
    {
        return $this->_sequence(
            Peg::stri($value)
        );
    }

    /**
     * @param string $value
     * @return Atom\Sequence
     */
    public function rex($value)
    {
        return $this->_sequence(
            Peg::rex($value)
        );
    }

    /**
     * @param string $value
     * @return Atom\Sequence
     */
    public function rexi($value)
    {
        return $this->_sequence(
            Peg::rexi($value)
        );
    }

    /**
     * @return Atom\Sequence
     */
    public function any()
    {
        return $this->_sequence(
            Peg::any()
        );
    }

    /**
     * @param string $name
     * @return Atom\Sequence
     */
    public function ref($name)
    {
        return $this->_sequence(
            Peg::ref($name)
        );
    }

    /**
     * @param mixed ... Variable number of arguments
     * @return Atom\Sequence
     */
    public function alt()
    {
        return $this->_sequence(
            call_user_func_array('Peg::alt', func_get_args())
        );
    }

    /**
     * @param mixed ... Variable number of arguments
     * @return Atom\Sequence
     */
    public function seq()
    {
        return $this->_sequence(
            call_user_func_array('Peg::seq', func_get_args())
        );
    }


    /**
     *
     * @param string $name
     * @return Atom\Ahead|Atom\Repeat|Atom\Sequence|null
     */
    public function __get($name)
    {
        switch ($name) {
            case 'repeat':
            case 'rep':
            case 'many':
            case 'zeroOrMore':
                return $this->repeat();
            case 'optional':
            case 'maybe':
            case 'zeroOrOne':
                return $this->optional();
            case 'once':
            case 'many1':
            case 'oneOrMore':
                return $this->repeat(1);
            case 'absent':
                return $this->not();
            case 'present':
                return $this->has();
            case 'ignore':
                $this->ignored = true;
                return $this;
            default:
                return NULL;
        }
    }

    public function __call($fn, $args)
    {
        // Since "as" is a reserved keyword we need to process it here
        if ('as' === $fn) {
            return $this->name($args[0]);
        }

        // Otherwise create rule references with the method name
        $atom = $this->ref($fn);
        // If an argument is supplied use it to create a named atom
        if (count($args) && !empty($args[0])) {
            $atom = $atom->name($args[0]);
        }

        return $atom;
    }
}
