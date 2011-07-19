<?php

include_once 'library/DrSlump/SimpleParser.php';

include_once 'library/DrSlump/SimpleParser/Node.php';
include_once 'library/DrSlump/SimpleParser/Node/Child.php';
include_once 'library/DrSlump/SimpleParser/Node/NonTerminal.php';
include_once 'library/DrSlump/SimpleParser/Node/Terminal.php';
include_once 'library/DrSlump/SimpleParser/Node/Root.php';

include_once 'library/DrSlump/SimpleParser/Atom.php';
include_once 'library/DrSlump/SimpleParser/Atom/Ahead.php';
include_once 'library/DrSlump/SimpleParser/Atom/Alternates.php';
include_once 'library/DrSlump/SimpleParser/Atom/RegExp.php';
include_once 'library/DrSlump/SimpleParser/Atom/Repeat.php';
include_once 'library/DrSlump/SimpleParser/Atom/Sequence.php';
include_once 'library/DrSlump/SimpleParser/Atom/Chained.php';
include_once 'library/DrSlump/SimpleParser/Atom/String.php';
include_once 'library/DrSlump/SimpleParser/Atom/Reference.php';

include_once 'library/DrSlump/SimpleParser/Grammar.php';
include_once 'library/DrSlump/SimpleParser/Source.php';

include_once 'library/DrSlump/SimpleParser/dsl.php';

use DrSlump\SimpleParser\Grammar;


class MyGrammar extends Grammar
{
    protected function rules()
    {
        //$this['term'] = alt(
        //    ref('factor')->str('+')->ref('term'),
        //    ref('factor')->str('-')->ref('term'),
        //    ref('term')
        //);
        //
        //$this['term'] = alt(
        //    seq( ref('factor'), '+', ref('term') ),
        //    seq( ref('factor'), '-', ref('term') ),
        //    seq( ref('factor') )
        //);

        $this['factor'] = rex('[0-9]');

        $this['term'] = seq(
            str('//'),
            seq( rex('\n')->absent, any() )->repeat
        );

        $this['term'] = str('//') -> rex('[^\n]')->repeat;

        $this['term'] = seq( '//', rex('[^\n]')->repeat ->as('comment') );


        $this['root'] = $this['term'];
    }
}

$atom = new \DrSlump\SimpleParser\Atom\RegExp('foo');

$grammar = new MyGrammar();

var_dump($grammar);

$grammar->parse("// fooOfoo");//1+2');

Root:
  - comment (fooOfoo)

1 + 2 + 3

Root:
  - left:
    - int (1)
  - op (+)
  - right:
    - left:
      - int (2)
    - op:
      - op (+)
    - right:
      - int (3)

foo(1, 2)

Root:
  - func (foo)
  - args
    - int(1)
    - int(2)

exit;







/**
* A runtime lexer generator which obtains the lexical rules from annotations.
*
* Copyright 2009 (c) Iván -DrSlump- Montes <drslump@pollinimini.net>
* Released to the Public Domain
*
* It supports the following annotations:
*
*  - @lexer-mode
*
*      Sets the matching mode for the lexing engine. It can be either "first"
*      or "longest". The former will consume the first rule that matches while
*      the later will try to match all the rules and use the one that matches
*      the longer amount of text.
*      This annotation can only be placed at the class level.
*
*  - @lexer-pattern:
*
*      Defines a pattern to use in rules. The first value is an identifier,
*      the second is a PHP compatible regular expression without delimiters.
*      This annotation can be placed at class and method levels.
*
*  - @lexer-rule:
*
*      Associates the given rule with the class method just below its comment
*      block. It's composed of an optional lexer state name ("START" if not
*      defined) between < and > characters and one or more pattern identifiers or
*      double quotes enclosed strings.
*      This annotation can only be placed at the method level.
*
* Example:
*
*  /**
*   * @lexer-mode first
*   * @lexer-pattern whitespace [ \t\r\n]+
*   * /
*  class MyLexer extends Lexer {
*      /** @lexer-rule whitespace    +/
*      function _space() {
*          return self::TOKEN_IGNORE;
*      }
*
*      /**
*       * @lexer-pattern   ident ([A-Za-z_]+)
*       * @lexer-rule      "$" ident
*       +/
*      function _ident($m) {
*          echo "Ident found: " . $m[1] . "\n";
*          $this->_token = MyParser::TKN_IDENT;
*      }
*
*      /** @lexer-rule "'"   +/
*      function _stringStart() {
*          $this->pushState('STRING');
*          return self::TOKEN_IGNORE;
*      }
*
*      /**
*       * @lexer-pattern   text [^']*
*       * @lexer-rule      <STRING> text
*       +/
*      function _stringText() {
*          $this->_token = MyParser::TKN_STRING;
*      }
*
*      /** @lexer-rule <STRING> "'"    +/
*      function _stringEnd() {
*          $this->popState();
*      }
*  }
*
                                                                               */
class Lexer implements Iterator {

    // Scanner modes
    const MODE_FIRST = 'first';
    const MODE_LONGEST = 'longest';

    /**
     * Consume the token and advance
     */
    const TOKEN_ACCEPT = null;
    /**
     * Repeat at the same position (in another state)
     */
    const TOKEN_REPEAT = true;
    /**
     * Ignore the token and advance
     */
    const TOKEN_IGNORE = false;
    /**
     * Repeat at the same position skiping the rule
     */
    const TOKEN_SKIP = 'skip';

    // Cache the lexer configuration for each class used
    private static $__lexers = array();

    // Internal data
    private $__mode = self::MODE_FIRST;
    private $__classname;
    private $__object;
    private $__stack = array();
    private $__state;

    // Lexer current position in the data
    protected $__line = 0;
    protected $__pos = 0;
    protected $__col = 0;

    protected $_data;
    protected $_token;
    protected $_value;

    /**
     * Constructor
     *
     * @param string $data              The text to scan
     * @param stirng | object $class    Either a class name or an object
     */
     function __construct($data = '', $class = null)
    {
        $this->__state = 'START';

        $this->_data = $data;

        if (NULL === $class) {
            $this->__classname = get_class($this);
            $this->__object = $this;
        } else if (is_string($class)) {
            $this->__classname = $class;
            $this->__object = $class;
        } else {
            $this->__classname = get_class($class);
            $this->__object = $class;
        }
    }

    public function getLine()
    {
        return $this->__line;
    }

    public function getOffset()
    {
        return $this->__pos;
    }

    public function getColumn()
    {
        return $this->__col;
    }

    /**
     * Changes the lexer state
     *
     * @param string $state The new lexer state
     * @return void
     */
    public function setState( $state )
    {
        $lexer = $this->__lexer();
        if (!isset($lexer['states'][$state])) {
            $states = array_keys($lexer['states']);
            throw new Exception('State ' . $state . ' not found. Available states: "' .
                                 implode('", "', $states) . '"');
        }

        $this->__state = $state;
    }

    /**
     * Returns the current state
     *
     * @return string
     */
    public function getState()
    {
        return $this->__state;
    }

    /**
     * Pushes the current state into a stack and sets a new one
     *
     * @param string $state
     * @return void
     */
    public function pushState( $state )
    {
        $this->__stack[] = $this->__state;
        $this->setState($state);
    }

    /**
     * Pops the top state from the stack and makes it the current one
     *
     * @return void
     */
    public function popState()
    {
        if (!count($this->__stack)) {
            throw new Exception('States stack is empty, unable to pop from state "' .
                                 $this->__state . '"');
        }
        $this->setState(array_pop($this->__stack));
    }

    /**
     * Returns the current token valud
     *
     * @return string
     * @see Iterator
     */
    public function current()
    {
        return $this->_value;
    }

    /**
     * Returns the current token identifier
     *
     * @return int
     * @see Iterator
     */
    public function key()
    {
        return $this->_token;
    }

    /**
     * Resets the lexer to make it as it was just initialized
     *
     * @param string | null $data   If set changes the data string to tokenize
     * @see Iterator
     */
    public function rewind()
    {
        $this->__state = 'START';
        $this->__stack = array();

        $this->__line = 0;
        $this->__pos = 0;
        $this->__col = 0;

        $this->_token = null;
        $this->_value = null;

    }

    /**
     * Tells if the lexer is ready to consume more tokens
     *
     * @return bool
     * @see Iterator
     */
    public function valid()
    {
        return $this->__pos < strlen($this->_data);
    }

    /**
     * Consumes the next token from the data
     *
     * @return void
     * @see Iterator
     */
    public function next()
    {
        $lexer = $this->__lexer();

        if ($lexer['mode'] === self::MODE_LONGEST) {
            $this->__matchLongest();
        } else {
            $this->__matchFirst();
        }
    }

    /**
     * Use this method to apply a lexer definition to skip the runtime building
     * process.
     *
     * @param array $definition     A lexer definition
     * @return void
     * @see getLexerDefinition
     */
    public function setLexerDefinition($definition)
    {
        self::$__lexers[ $this->__classname ] = $definition;
        $this->rewind();
    }

    /**
     * Returns the lexer definition so it can be persisted to improve performance
     *
     * @return array
     * @see setLexerDefinition
     */
    public function getLexerDefinition()
    {
        return $this->__lexer();
    }

    /**
     * Tries to match the token with a first-to-match policy
     *
     * @return void
     */
    private function __matchFirst()
    {
        // Check if the input is over
        if ($this->__pos >= strlen($this->_data)) {
            return;
        }

        $lexer = $this->__lexer();
        $skip = array();

        do {
            $state = $this->__state;

            // Use the pregenerated regular expression
            if (empty($skip)) {
                $regexp = $lexer['regexps'][ $state ];
            // Build the regexp ignoring the skipped tokens
            } else {
                $regexp = array();
                foreach($lexer['states'][$state] as $idx => $rule) {
                    if (!in_array($idx, $skip)) {
                        $regexp[] = $rule['regexp'];
                    } else {
                        // Add an impossible to match regexp in its place
                        $regexp[] = '$^';
                    }
                }
                $regexp = '@(' . implode(')|(', $regexp) . ')@Ax';
            }

            if (preg_match($regexp, $this->_data, $submatches, null, $this->__pos)) {
                // Remove empty sub-patterns
                $matches = array_filter($submatches, 'strlen');
                if (!count($matches)) {
                    throw new Exception(
                        'Lexer failed because a rule matched an empty string ' .
                        'in state "' . $this->__state . '"'
                    );
                }

                // Skip global match
                next($matches);

                // Get lexer token and value
                $idx = key($matches);
                $this->_value = $value = current($matches);

                // Adjust subpatterns count to get the correct index
                $token = 0; $subs = 1;
                while ($subs < $idx) {
                    $subs += 1 + $lexer['states'][$state][$token]['subs'];
                    $token++;
                }

                $this->_token = $token;

                // Extract submatches
                $subs = $lexer['states'][$state][$token]['subs'];
                $submatches = array_slice($submatches, $idx, $subs);

                // Check the rule result
                $result = $this->__result($token, $value, $submatches);
                if ($result === TRUE) {
                    return;
                } else if ($result === FALSE) {
                    $skip = array();
                    continue;
                } else {
                    $skip[] = $result;
                    continue;
                }

            } else {
                throw new Exception(
                    'Unexpected input at line' . $this->__line . ' column ' .
                    $this->__col . ': ' . $this->_data[$this->__pos]
                );
            }
            break;
        } while (true);
    }


    /**
     * Tries to match the token with a longest-match policy
     *
     * @return void
     */
    public function __matchLongest()
    {
        // Check if the input is over
        if ($this->__pos >= strlen($this->_data)) {
            return false;
        }

        $lexer = $this->__lexer();
        $skip = array();

        do {
            $state = $this->__state;
            $token = 0;
            $value = false;
            $submatches = array();
            foreach ($lexer['states'][$state] as $i => $rule) {
                $re = '@' . $rule['regexp'] . '@ASx';
                if (!in_array($i, $skip) &&
                    preg_match($re, $this->_data, $matches, null, $this->__pos)) {
                    // Check if we found a longer token
                    if ( $value === false || strlen($matches[0]) > strlen($value) ) {
                        $value = $matches[0];
                        $submatches = $matches;
                        $token = $i;
                    }
                }
            }

            if ($value === false) {
                throw new Exception(
                    'Unexpected input at line ' . $this->_line . ' column ' .
                    $this->__col . ':  ' . $this->_data[$this->__pos]
                );
            }

            $this->_token = $token;
            $this->_value = $value;

            // Check the rule result
            $result = $this->__result($token, $value, $submatches);
            if ($result === TRUE) {
                return;
            } else if ($result === FALSE) {
                $skip = array();
                continue;
            } else {
                $skip[] = $result;
            }

        } while (true);
    }

    /**
     * Calls the associated method to a rule
     *
     * If this function returns TRUE means that the token has been accepted.
     * If the return value is FALSE means that the token should be ignored.
     * If it is an integer it means a rule index and it should be skipped.
     *
     * @param int $ruleIdx      The matched rule index
     * @param string $value     The matched value
     * @param array $submatches The submatches to pass to the method
     * @return boolean | string
     */
    protected function __result($ruleIdx, $value, $submatches)
    {
        $lexer = $this->__lexer();

        // Keep the current state to detect changes
        $state = $this->__state;

        // Call the handler
        $method = $lexer['states'][$state][$ruleIdx]['method'];
        $result = call_user_func( array($this->__object, $method), $submatches );

        if ($result === self::TOKEN_ACCEPT) {
            $this->__pos += strlen($value);
            $this->__line += substr_count($value, "\n");
            if ($pos = strrpos($value, "\n")) {
                $this->__col = strlen($value) - $pos;
            } else {
                $this->__col += strlen($value);
            }

            // accept this token
            return true;
        } elseif ($result === self::TOKEN_REPEAT) {
            if ($state === $this->__state) {
                throw new Exception('Rule signalled a repeat but didn\'t change the lexer state');
            }

            // we have changed state process this token in the new state
            $this->next();
            return false;

        } elseif ($result === self::TOKEN_IGNORE) {
            $this->__pos += strlen($value);
            $this->__line += substr_count($value, "\n");
            if ($pos = strrpos($value, "\n")) {
                $this->__col = strlen($value) - $pos;
            } else {
                $this->__col += strlen($value);
            }

            // end of input
            if ($this->__pos >= strlen($this->_data)) {
                return true;
            }

            // skip this token
            return false;
        } elseif ($result === self::TOKEN_SKIP) {
            if ($state === $this->__state) {
                return $ruleIdx;
            } else {
                // We have changed the state so this is like IGNORE
                $this->next();
                return true;
            }
        } else {
            throw new Exception(
                'Rule returned an invalid value. Only NULL, TRUE and FALSE ' .
                'and "skip" values are allowed'
            );
        }
    }


    /**
     * Returns the lexer data structure and triggers the build process if needed
     *
     * @return array    The lexer data structure
     */
    protected function __lexer()
    {
        if (!isset(self::$__lexers[$this->__classname])) {
            self::$__lexers[$this->__classname] = $this->__build();
        }
        return self::$__lexers[$this->__classname];
    }

    /**
     * Builds the lexer data structure by following the annotations found in the
     * target class
     *
     * @return array    The data structure
     */
    protected function __build()
    {
        $states = array();
        $regexps = array();
        $patterns = array();
        $rules = array();

        $refl = new ReflectionClass($this->__classname);

        // Get global configuration
        $tags = $this->__getCommentTags( $refl->getDocComment() );
        foreach($tags as $tag) {
            if ($tag[0] === 'lexer-pattern') {
                if (!preg_match('/^([A-Za-z_]+)\s+(.+)$/', $tag[1], $m)) {
                    throw new Exception('Malformed @lexer-alias: ' . $tag[1]);
                }

                $patterns[$m[1]] = rtrim($m[2]);
            } else if ($tag[0] === 'lexer-mode') {
                if (!preg_match('/^(longest|first)\s*$/', $tag[1], $m)) {
                    throw new Exception('Malformed @lexer-mode. Only "longest" and "first" are allowed. Found: ' . $tag[1]);
                }

                $this->__mode = $m[1] === 'first' ? self::MODE_FIRST : self::MODE_LONGEST;
            }
        }

        // Get function rules
        $methods = $refl->getMethods();
        foreach($methods as $method) {
            $tags = $this->__getCommentTags( $method->getDocComment() );
            foreach ($tags as $tag) {

                if ($tag[0] === 'lexer-pattern') {

                    if (!preg_match('/^([A-Za-z_]+)\s+(.*)$/', $tag[1], $m)) {
                        throw new Exception('Malformed @lexer-alias: ' . $tag[1]);
                    }

                    $patterns[$m[1]] = rtrim($m[2]);

                } else if ($tag[0] === 'lexer-rule') {

                    if (!preg_match('/^(?:<([A-Za-z_]+)>\s+)?(.+)$/', $tag[1], $m)) {
                        throw new Exception('Malformed @lexer-parser: ' . $tag[1]);
                    }

                    $rules[] = array(
                        'state'     => $m[1] ? $m[1] : 'START',
                        'method'    => $method->getName(),
                        'expression'=> trim($m[2])
                    );
                }
            }
        }

        // Build the states
        foreach ($rules as $rule) {
            // Create the state if doesn't exists yet
            if (!isset($states[ $rule['state'] ])) {
                $states[ $rule['state'] ]  = array();
            }

            $expr = $rule['expression'] . ' ';
            $regexp = '';
            $pos = 0;
            while (preg_match('/"([^"]+)"\s+|([A-Za-z_][A-Za-z0-9_]*)\s+/', $expr, $m, null, $pos)) {
                $pos += strlen($m[0]);

                if (!empty($m[1])) {
                    $regexp .= preg_quote($m[1]);
                } else {
                    if (!isset($patterns[$m[2]])) {
                        throw new Exception('Pattern not found: ' . $m[2]);
                    }
                    $regexp .= $patterns[$m[2]];
                }
            }
            if ($pos < strlen($expr)) {
                throw new Exception('Malformed rule expression: ' . $rule['expression']);
            }

            // Calculate the number of subpatterns in the regular expression
            // Remove escaped backslashes
            $str = str_replace('\\\\', '', $regexp);
            // Remove escaped parenthesis
            $str = str_replace('\\(', '', $str);
            // Remove non-capturing sets
            $str = str_replace('(?:', '', $str);
            $count = substr_count($str, '(');

            $states[ $rule['state'] ][] = array(
                'method' => $rule['method'],
                'regexp' => str_replace('@', '\@', $regexp),
                'subs'   => $count
            );
        }

        // For first-match mode build the global regexps and mapping
        if ($this->__mode === 'first') {
            foreach ($states as $state => $rules) {
                $res = array();
                foreach($rules as $rule) {
                    $res[] = $rule['regexp'];
                }
                $regexps[$state] = '@(' . implode(')|(', $res) . ')@ASx';
            }
        }

        // Store the lexer
        return array(
            'mode'          => $this->__mode,
            'states'        => $states,
            'regexps'       => $regexps
        );
    }

    /**
     * Parses a comment looking for annotations
     *
     * @param string $comment
     * @return array
     */
    private function __getCommentTags($comment)
    {
        $tags = array();

        if (strpos($comment, '/**') !== 0) {
            return $tags;
        }

        // Remove start and end marks
        $comment = substr($comment, 3, -2);

        $lines = explode("\n", $comment);
        foreach ($lines as $ln) {
            $ln = ltrim($ln, " \t*");
            if (preg_match('/^@([A-Za-z_][A-Za-z_-]*)\s+(.*)$/', $ln, $m)) {
                $tags[] = array( $m[1], $m[2] );
            }
        }
        return $tags;
    }

}


