<?php

namespace DrSlump;

use \DrSlump\Peg\Atom;


class Peg
{
    static $grammars = array();

    //static $charArt = array('┣ ', '┗ ', '┇ ', '  ');
    //static $charArt = array('┣ ', '╰ ', '┇ ', '  ');
    //static $charArt = array('├ ', '└ ', '│ ', '  ');
    static $charArt = array('|- ', '`- ', '|  ', '   ');
    //static $charArt = array('- ', '- ', '  ', '  ');
    //static $charArt = array('  ',  '  ', '  ', '  ');

    /**
     * Setup SPL autoloader for the library classes
     *
     * @static
     * @return void
     */
    static public function autoload()
    {
        spl_autoload_register(function($class){
            $prefix = __CLASS__ . '\\';
            if (strpos($class, $prefix) === 0) {
                // Remove vendor from name
                $class = substr($class, strlen(__NAMESPACE__)+1);
                // Convert namespace separator to directory ones
                $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
                // Prefix with this file's directory
                $class = __DIR__ . DIRECTORY_SEPARATOR . $class;

                include($class . '.php');
                return true;
            }

            return false;
        });
    }

    static public function getActiveGrammar()
    {
        return self::$grammars[ count(self::$grammars) - 1 ];
    }

    static public function pushGrammar($grammar)
    {
        self::$grammars[] = $grammar;
    }

    static public function popGrammar()
    {
        return array_pop(self::$grammars);
    }


    static public function argument($value)
    {
        if ($value instanceof Atom) return $value;

        $wrappers = substr($value, 0, 1) . substr($value, -1);

        if ($wrappers === '<>') {

            // This is really ugly but we need a way to pass around the active grammar
            $grammar = self::getActiveGrammar();

            // References can be named
            $value = substr($value, 1, -1);
            if (FALSE !== strpos($value, ':')) {
                list($name, $ref) = explode(':', $value, 2);
                $name = strlen($name) > 0 ? $name : $ref;
                $atom = new Atom\Reference($grammar, $ref);
                $atom->desc($name);
            } else {
                $atom = new Atom\Reference($grammar, $value);
            }

        } else if (strlen($value) > 2 && $wrappers === '//') {

            $atom = new Atom\RegExp(substr($value, 1, -1));

        } else {
            $atom = new Atom\String($value);
        }

        return $atom;
    }

    static public function arguments($args)
    {
        foreach ($args as $k=>$arg) {
            $args[$k] = self::argument($arg);
        }

        return $args;
    }


    static public function alt()
    {
        $args = self::arguments(func_get_args());
        return new Atom\Alternates($args);
    }

    static public function str($value)
    {
        return new Atom\String($value);
    }

    static public function stri($value)
    {
        return new Atom\String($value, true);
    }

    static public function rex($pattern)
    {
        return new Atom\RegExp($pattern);
    }

    static public function rexi($pattern)
    {
        return new Atom\RegExp($pattern, true);
    }

    static public function seq()
    {
        $args = self::arguments(func_get_args());
        return new Atom\Sequence($args);
    }

    static public function ref($name)
    {
        // This is very ugly but we need a way to pass around the active grammar
        $grammar = self::getActiveGrammar();
        return new Atom\Reference($grammar, $name);
    }

    static public function any()
    {
        return self::alt(
            self::rex('.'),
            self::str("\n")
        );
    }

    static public function not($value)
    {
        $value = self::argument($value);
        return new Atom\Ahead($value, false);
    }
}
