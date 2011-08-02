<?php

namespace DrSlump\Peg\Source;

interface SourceInterface
{

    /**
     * Read N chars from the stream
     *
     * @abstract
     * @param int $charsNo
     * @return string
     */
    public function read($charsNo);

    /**
     * Read the stream until the end of line is found, returning everything upto but not including
     * the end of line character or until the end of the stream.
     *
     * This is specially useful for the regexp matchers, since this way they can work on more than
     * a character at a time.
     *
     * @abstract
     * @param string $ch
     * @return string
     */
    public function readLn();

    /**
     * Check if we have reached the end of the stream
     *
     * @abstract
     * @return bool
     */
    public function eof();

    /**
     * Obtain the current offset in the stream
     *
     * @abstract
     * @return int
     */
    public function tell();

    /**
     * Change the current offset in the stream
     *
     * @abstract
     * @param int $byteOffset
     * @return void
     */
    public function seek($byteOffset);

    /**
     * Get current column
     *
     * @abstract
     * @return int
     */
    public function column();

    /**
     * Get current row
     *
     * @abstract
     * @return int
     */
    public function row();


    /**
     * Compares a string against the source contents
     *
     * @param string $expected
     * @param bool $caseInsensitive
     * @return string | false
     */
    public function compare($expected, $caseInsensitive = FALSE);

    /**
     * Matches a regular expression against the current line
     *
     * @param string $expr
     * @param bool $caseInsensitive
     * @return string | false
     */
    public function match($expr, $caseInsensitive = FALSE);
}
