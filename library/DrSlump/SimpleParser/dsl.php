<?php

// PHP doesn't allow to use functions in a namespace without a prefix
// so we need to pollute the global namespace for the DSL keywords :(

use DrSlump\SimpleParser as SP;
use DrSlump\SimpleParser\Atom;


/**
 * @return \DrSlump\SimpleParser\Atom\Alternates
 */
function alt() {
    return call_user_func_array('\DrSlump\SimpleParser::alt', func_get_args());
}

/**
 * @return \DrSlump\SimpleParser\Atom\String
 */
function str($value) {
    return SP::str($value);
}

/**
 * @return \DrSlump\SimpleParser\Atom\String
 */
function stri($value) {
    return SP::stri($value);
}

/**
 * @return \DrSlump\SimpleParser\Atom\RegExp
 */
function rex($pattern) {
    return SP::rex($pattern);
}

/**
 * @return \DrSlump\SimpleParser\Atom\RegExp
 */
function rexi($pattern) {
    return SP::rexi($pattern);
}

/**
 * @return \DrSlump\SimpleParser\Atom\Sequence
 */
function seq() {
    return call_user_func_array('\DrSlump\SimpleParser::seq', func_get_args());
}

/**
 * @return \DrSlump\SimpleParser\AAtom\Reference
 */
function ref($name) {
    return SP::ref($name);
}

/**
 * @return \DrSlump\SimpleParser\AAtom\Regexp
 */
function any() {
    return SP::any();
}

/**
 * @return \DrSlump\SimpleParser\AAtom\Ahead
 */
function not($value) {
    return SP::not($value);
}
