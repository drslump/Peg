<?php

namespace DrSlump\Peg\Packrat;

use DrSlump\Peg\Atom;

/**
 * Standard Packrat implementation which memoizes every result for every
 * atom processed at every position in the source stream. Watch out for
 * memory consumption when using large grammars or input sources.
 *
 */
class Standard implements PackratInterface
{
    /** @var array */
    protected $cache = array();


    public function has(Atom $atom, $ofs)
    {
        $hash = spl_object_hash($atom);
        return isset($this->cache["$ofs:$hash"]);
    }

    public function get(Atom $atom, $ofs, &$length)
    {
        $hash = spl_object_hash($atom);
        $key = "$ofs:$hash";

        if (!isset($this->cache[$key])) {
            return NULL;
        }

        list($result, $length) = $this->cache[$key];
        return $result;
    }

    public function set(Atom $atom, $ofs, $result, $length = 0)
    {
        $hash = spl_object_hash($atom);
        $key = "$ofs:$hash";

        $this->cache[$key] = array($result, $length);
    }

    public function clear()
    {
        $this->cache = array();
    }
}
