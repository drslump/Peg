<?php

namespace DrSlump\SimpleParser;

use \DrSlump\SimpleParser;
use \DrSlump\SimpleParser\Source;

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
    /** @var \Closure */
    protected $cb;

    /** @var bool */
    protected $ignored = false;


    /**
     * Parses the given input. This is the method to use when you want to
     * process a source.
     *
     * @param string | \DrSlump\SimpleParser\Source $source
     * @return void
     */
    public function parse($source)
    {
        if (is_string($source)) {
            $source = new Source($source);
        }

        $result = $this->apply($source);

        if (!$source->eof()) {
            throw new \Exception('Parser terminated before end of file');
        }
    }

    /**
     * Calls the match() method of the atom taking care of errors
     *
     * @param \DrSlump\SimpleParser\Source $source
     * @return mixed
     */
    public function apply($source)
    {
        $ofs = $source->pos();

        $result = $this->match($source);
        if (FALSE === $result) {
            $source->pos($ofs);
            return FALSE;
        }

        if (is_callable($this->cbChange)) {
            $result = call_user_func($this->cbChange, $result);
            if (FALSE === $result) {
                $source->pos($ofs);
                return FALSE;
            }
        }

        return $result;
    }

    /**
     * Checks if the atom matches the source.
     *
     * @param \DrSlump\SimpleParser\Source $source
     * @return false | string
     */
    protected function match(Source $source)
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
            $value = SimpleParser::argument($value);
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
            $value = SimpleParser::argument($value);
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
            SimpleParser::str($value)
        );
    }

    /**
     * @param string $value
     * @return Atom\Sequence
     */
    public function stri($value)
    {
        return $this->_sequence(
            SimpleParser::stri($value)
        );
    }

    /**
     * @param string $value
     * @return Atom\Sequence
     */
    public function rex($value)
    {
        return $this->_sequence(
            SimpleParser::rex($value)
        );
    }

    /**
     * @param string $value
     * @return Atom\Sequence
     */
    public function rexi($value)
    {
        return $this->_sequence(
            SimpleParser::rexi($value)
        );
    }

    /**
     * @return Atom\Sequence
     */
    public function any()
    {
        return $this->_sequence(
            SimpleParser::any()
        );
    }

    /**
     * @param string $name
     * @return Atom\Sequence
     */
    public function ref($name)
    {
        return $this->_sequence(
            SimpleParser::ref($name)
        );
    }

    /**
     * @param mixed ... Variable number of arguments
     * @return Atom\Sequence
     */
    public function alt()
    {
        return $this->_sequence(
            call_user_func_array('SimpleParser::alt', func_get_args())
        );
    }

    /**
     * @param mixed ... Variable number of arguments
     * @return Atom\Sequence
     */
    public function seq()
    {
        return $this->_sequence(
            call_user_func_array('SimpleParser::seq', func_get_args())
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
        // If an argument is supplied use it to created a named atom
        if (count($args) && !empty($args[0])) {
            $atom = $atom->name($args[0]);
        }

        return $atom;
    }
}
