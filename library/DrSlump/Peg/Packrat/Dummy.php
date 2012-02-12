<?php

namespace DrSlump\Peg\Packrat;

use DrSlump\Peg\Atom;


/**
 * Dummy Packrat implementation used by default which DOES NOT memoize
 * anything at all, so that memory consumption is not affected but the parser
 * could run in exponential time for worst cases.
 *
 */
class Dummy implements PackratInterface
{
    public function has(Atom $atom, $ofs)
    {
        return false;
    }

    public function get(Atom $atom, $ofs, &$length)
    {
        return null;
    }

    public function set(Atom $atom, $ofs, $result, $length = 0)
    {
        // Nothing to do
    }

    public function clear()
    {
        // Nothing to do
    }
}
