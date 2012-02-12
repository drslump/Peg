<?php

namespace DrSlump\Peg\Packrat;

use DrSlump\Peg\Atom;

/**
 * Packrat implementation with a fixed pool of memoized results. When the
 * limit is reached oldest entries are freed (a FIFO queue).
 *
 */
class Fixed extends Standard implements PackratInterface
{
    const DEFAULT_SIZE = 1000;

    /** @var \SplQueue */
    protected $hashes;
    /** @var array */
    protected $cache = array();
    /** @var int */
    protected $limit;


    public function __construct($size = self::DEFAULT_SIZE)
    {
        $this->clear();
        $this->limit = $size;
    }

    public function set(Atom $atom, $ofs, $result, $length = 0)
    {
        // Remove oldest entry if we have reached the limit
        if ($this->limit <= count($this->hashes)) {
            $key = $this->hashes->dequeue();
            // Try to be more memory efficient by assigning null before unsetting
            $this->cache[$key] = NULL;
            unset($this->cache[$key]);
        }

        $hash = spl_object_hash($atom);
        $key = "$ofs:$hash";

        $this->hashes->enqueue($key);
        $this->cache[$key] = array($result, $length);
    }

    public function clear()
    {
        $this->cache = array();
        $this->hashes = new \SplQueue();
    }
}
