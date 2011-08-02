<?php

namespace DrSlump\Peg\Source;

class Utf8 extends Ascii implements SourceInterface, Utf8Interface
{
    const BOM = "\xEF\xBB\xBF";

    public function read($charsNo)
    {
        // Ignore the BOM if present
        if (0 === $this->tell()) {
            $bom = parent::read(strlen(self::BOM));
            if (self::BOM !== $bom) {
                $this->seek(0);
            }
        }

        // Note that this algo requires to start at the beginning of an UTF8 sequence
        $result = '';
        while ($charsNo--) {
            $ch = parent::read(1);
            $ord = ord($ch);
            if ($ord >= 0xF0) {
                $result .= $ch . parent::read(3);
            } else if ($ord >= 0xE0) {
                $result .= $ch . parent::read(2);
            } else if ($ord >= 0xC0) {
                $result .= $ch . parent::read(1);
            } else {
                // Note that malformed UTF8 are treated as plain characters too
                $result .= $ch;
            }
        }

        return $result;
    }

    /**
     * Compares a string against the source contents
     *
     * @param string $expected
     * @param bool $caseInsensitive
     * @return string | false
     */
    public function compare($expected, $caseInsensitive = FALSE)
    {
        // Use parent (ASCII) read to avoid having to count UTF8 characters
        $value = parent::read(strlen($expected));

        if ($caseInsensitive) {
            return 0 === mb_stripos($value, $expected, 0, 'UTF-8') ? $value : FALSE;
        }

        return $value === $expected ? $value : FALSE;
    }


    /**
     * Matches a regular expression against the current line
     *
     * @param string $expr
     * @param bool $caseInsensitive
     * @return string | false
     */
    public function match($expr, $caseInsensitive = FALSE)
    {
        $ofs = $this->tell();
        $ln = $this->readLn();

        // Add modifiers
        $expr .= $caseInsensitive ? 'ui' : 'u';

        if (!preg_match($expr, $ln, $m)) {
            $this->seek($ofs);
            return false;
        }

        // Adjust the source pointer
        $this->seek( $ofs + strlen($m[0]) );
        return $m[0];
    }
}
