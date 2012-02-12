<?php

namespace DrSlump\Peg\Source;

use DrSlump\Peg\Failure;
use DrSlump\Peg\Atom;

class Ascii implements SourceInterface
{
    /** @var bool */
    protected $closeOnDestroy = false;
    /** @var \resource */
    protected $fd;
    /** @var \SplStack */
    protected $eol;

    public function __construct($stringOrFd)
    {
        if (is_string($stringOrFd)) {
            $this->closeOnDestroy = true;
            $stringOrFd = fopen('data://text/plain;base64,' . base64_encode($stringOrFd), 'r');
        }

        if (!is_resource($stringOrFd)) {
            throw new \RuntimeException('The given argument is not a valid resource');
        }

        $this->fd = $stringOrFd;

        // Instantiate the stack object for the line endings
        $this->eol = new \splStack();
    }

    public function __destruct()
    {
        if ($this->closeOnDestroy && is_resource($this->fd)) {
            fclose($this->fd);
        }
    }

    /**
     * Read N chars from the stream
     *
     * @param int $charsNo
     * @return string | false
     */
    public function read($charsNo)
    {
        // Save current offset
        $offset = $this->tell();

        // Obtain characters from the stream
        $value = fread($this->fd, $charsNo);
        if (strlen($value) !== $charsNo || FALSE === $value) {
            return FALSE;
        }

        // Register end of line offsets in the read value
        $from = 0;
        while (FALSE !== ($pos = strpos($value, "\n", $from))) {
            $this->eol->push($offset + $pos);
            $from = $pos + 1;
        }

        return $value;
    }

    /**
     * Read the stream until the end of line is found, returning everything upto but not including
     * the end of line character or until the end of the stream.
     *
     * This is specially useful for the regexp matchers, since this way they can work on more than
     * a character at a time.
     *
     * @return string | false
     */
    public function readLn()
    {
        $ln = fgets($this->fd);
        if (FALSE === $ln) {
            return FALSE;
        }

        // Register the end of line offset
        $this->eol->push($this->tell());

        return $ln;
    }

    /**
     * Check if we have reached the end of the stream
     *
     * @return bool
     */
    public function eof()
    {
        return feof($this->fd);
    }

    /**
     * Obtain the current offset in the stream
     *
     * @return int
     */
    public function tell()
    {
        return ftell($this->fd);
    }

    /**
     * Change the current offset in the stream
     *
     * @param int $byteOffset
     * @return void
     */
    public function seek($byteOffset)
    {
        // Clean up eol's above the new offset
        while (!$this->eol->isEmpty() && $this->eol->top() > $byteOffset) {
            $this->eol->pop();
        }

        fseek($this->fd, $byteOffset);
    }

    /**
     * Get current column
     *
     * @return int
     */
    public function column()
    {
        $ofs = $this->tell() ?: 0;
        return $this->eol->isEmpty()
               ? $ofs
               : $ofs - $this->eol->top();
    }

    /**
     * Get current row
     *
     * @return int
     */
    public function row()
    {
        return $this->eol->count() ?: 0;
    }

    /**
     * Compares a string against the source contents
     *
     * @param string $expected
     * @param bool $caseInsensitive
     * @return string | \DrSlump\Peg\Failure
     */
    public function compare($expected, $caseInsensitive = FALSE)
    {
        $value = $this->read(strlen($expected));

        if (FALSE === $value || strlen($value) < strlen($expected)) {
            return new Failure('Premature end of input');
        }

        PEG_DEBUG and print("Comparing >$value< with >$expected<\n");

        if ($caseInsensitive && 0 === stripos($value, $expected)) {
            return $value;
        } else if ($value === $expected) {
            return $value;
        }

        return new Failure("Expected $expected, but got $value");
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

        if (FALSE === $ln) {
            return FALSE;
        }

        // Add modifiers
        if ($caseInsensitive) {
            $expr .= 'i';
        }

        PEG_DEBUG and print("Matching $expr against >$ln<\n");

        if (!preg_match($expr, $ln, $m)) {
            return FALSE;
        }

        // Adjust the source pointer
        $this->seek($ofs + strlen($m[0]));
        return $m[0];
    }

}
