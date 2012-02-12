<?php

namespace DrSlump\Peg\Packrat;

use DrSlump\Peg\Atom;

interface PackratInterface
{
    public function has(Atom $atom, $ofs);
    public function get(Atom $atom, $ofs, &$length);
    public function set(Atom $atom, $ofs, $result, $length = 0);
    public function clear();
}
