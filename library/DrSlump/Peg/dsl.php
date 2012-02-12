<?php

// PHP doesn't allow to use functions in a namespace without a prefix
// so we need to pollute the global namespace for the DSL keywords :(

use DrSlump\Peg as P;
use DrSlump\Peg\Atom;


/**
 * @return \DrSlump\Peg\Atom\Alternates
 */
function alt() {
    return call_user_func_array('\DrSlump\Peg::alt', $args = func_get_args());
}

/**
 * @return \DrSlump\Peg\Atom\String
 */
function str($value) {
    return P::str($value);
}

/**
 * @return \DrSlump\Peg\Atom\String
 */
function stri($value) {
    return P::stri($value);
}

/**
 * @return \DrSlump\Peg\Atom\RegExp
 */
function rex($pattern) {
    return P::rex($pattern);
}

/**
 * @return \DrSlump\Peg\Atom\RegExp
 */
function rexi($pattern) {
    return P::rexi($pattern);
}

/**
 * @return \DrSlump\Peg\Atom\Sequence
 */
function seq() {
    return call_user_func_array('\DrSlump\Peg::seq', func_get_args());
}

/**
 * @return \DrSlump\Peg\Atom\Reference
 */
function ref($name) {
    return P::ref($name);
}

/**
 * @return \DrSlump\Peg\Atom\Regexp
 */
function any() {
    return P::any();
}

/**
 * @return \DrSlump\Peg\Atom\Ahead
 */
function not($value) {
    return P::not($value);
}
