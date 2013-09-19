<?php
/****************************
 * A CSV parsing library.   *
 * Copyright Maxime Mérian. *
 ****************************/

namespace Csv;

use Csv\Error;

/**
 * A CSV file reader
 *
 * @author Maxime Mérian
 *
 */
class Reader implements \Iterator
{
    protected $mode = 'rb';
    /**
     * Encoding of the file that will be read
     *
     * @var string
     */
    protected $inputEncoding = 'UTF-8';

    /**
     * Encoding of the data that will be returned by the reader
     *
     * @var string
     */
    protected $outputEncoding = 'UTF-8';

    /**
     * CSV delimiter
     *
     * @var string
     */
    protected $delimiter = ',';

    /**
     * CSV enclosure
     *
     * @var string
     */
    protected $enclosure = '"';

    /**
     * Does the CSV file hae a header with column names ?
     *
     * @var string
     */
    protected $hasHeader = false;

    /**
     * CSV file header
     *
     * @var array
     */
    protected $header = null;

    /**
     * Current line number in the CSV file that is being processed
     *
     * @var int
     */
    protected $curLine = 0;

    /**
     * Data the is currently being read
     *
     * @var array
     */
    protected $currentData = null;

    /**
     * Path to the open file, if the reader was instanciated with a file path
     *
     * @var string
     */
    protected $filePath = null;

    /**
     * Path to the file that was opened, if any.
     *
     * @var string
     */
    protected $file = null;

    /**
     * Pointer to the file that is being read
     *
     * @var resource
     */
    protected $fp = null;

    /**
     * Callback functions for formatting fields
     *
     * @var array
     */
    protected $formatters = array();

    /**
     * List of valid options
     *
     * @var array
     */
    protected $validOptions = array(
        'hasHeader',
        'header',
        'inputEncoding',
        'outputEncoding',
        'delimiter',
        'enclosure'
    );

    /**
     * Constructor
     *
     * @param string|resource $file The file to read. Can be provided as the path to the file or as a resource
     * @param array $options
     *
     * @throws Csv\Error
    */
    public function __construct($file, array $options = array())
    {
        if (is_resource($file)) {
            $this->fp = $file;
        } elseif (is_string($file)) {
            if (! file_exists($file)) {
                throw new Error($file . ' does not exist');
            } elseif (! is_readable($file)) {
                throw new Error($file . ' is not readable');
            }
            $this->file = $file;
        } else {
            throw new Error('File must be a valid path or resource');
        }

        $this->setOptions($options);

        $this->init();
    }

    protected function init()
    {
        $this->openFile();
        $this->rewind();
    }

    /**
     * Destructor.
     *
     * Closes the open CSV file if necessary
     */
    public function __destruct()
    {
        /*
         * Only close the resource if we opened it
         */
        if ($this->file && $this->fp) {
            fclose($this->fp);
        }
    }

    /**
     * Returns the CSV file header
     *
     * @return array|null
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Returns the pointer to the file being processed
     *
     * @return resource
     */
    public function getFp()
    {
        return $this->fp;
    }

    /**
     *
     * @param string $field
     * @param callback $callback
     */
    public function registerFormatter($field, $callback)
    {
        if (! is_callable($callback)) {
            throw new Error('Provided callback for field ' . $field . ' is not callable');
        }
        $this->formatters[$field] = $callback;
    }

    /**
     * Sets the reader options.
     *
     * @param array $options
     *
     * @return Csv\Reader
     */
    public function setOptions(array $options)
    {
        foreach ($options as $opt => $val) {
            $this->setOption($opt, $val);
        }

        return $this;
    }

    /**
     * Sets an option
     *
     * @param string $name
     * @param mixed $value
     *
     * @return Csv\Reader
     *
     * @throws Csv\Error
     */
    public function setOption($name, $value)
    {
        if (! in_array($name, $this->validOptions)) {
            throw new Error('Invalid option ' . $name . '. Valid options are : ' . join(', ', $this->validOptions));
        }
        // Check duplicate fields in header
        if ('header' == $name) {
            $cnt = array_count_values($value);
            $duplicates = array();
            foreach ($cnt as $f => $c) {
                if ($c > 1) {
                    $duplicates[$f] = $c;
                }
            }
            if (sizeof($duplicates) > 0) {
                $msg = 'Duplicate fields found in header : ' . join(', ', array_keys($duplicates));
                throw new Error($msg);
            }
        }
        $this->$name = $value;

        return $this;
    }

    /**
     * Opens the CSV file for read
     *
     * @return \Csv\Reader
     */
    protected function openFile()
    {
        if (is_null($this->fp)) {
            $this->fp = fopen($this->file, $this->mode);
            if (! $this->fp) {
                throw new Error('Unable to open ' . $this->file);
            }
        }

        return $this;
    }

    /**
     * Read the next line.
     *
     * If no new line can be read, this
     * method returns false
     *
     * @return array
     *
     * @throws Csv\Error if no line can be read
     */
    protected function readLine()
    {
        if (! $this->valid()) {
            throw new Error('End of stream reached, no data to read');
        }
        $this->currentData = fgetcsv($this->fp, null, $this->delimiter, $this->enclosure);
        if (! $this->valid()) {
            return false;
        }

        $this->curLine++;

        if ($this->inputEncoding != $this->outputEncoding) {
            $inEnc = $this->inputEncoding;
            $outEnc = $this->outputEncoding;
            array_walk($this->currentData, function(&$str) use ($inEnc, $outEnc) {
                $str = mb_convert_encoding($str, $outEnc, $inEnc);
            });
        }

        return $this->currentData;
    }

    /**
     * Returns an HTML table preview of the csv data
     *
     * @return string
     */
    public function getHtmlPreview($numLines = 5)
    {
        $html = '<table>';
        if ($this->header) {
            $html .= '<thead><tr>';
            foreach ($this->header as $h) {
                $html .= '<th>' . htmlentities($h, ENT_QUOTES, 'UTF-8') . '</th>';
            }
            $html .= '</tr></thead>';
        }
        $html .= '<tbody>';
        $i = 0;
        foreach ($this as $line) {
            if ($i >= $numLines) {
                break;
            }
            $html .= '<tr>';
            foreach ($line as $v) {
                $html .= '<td>' . htmlentities($v, ENT_QUOTES, 'UTF-8') . '</td>';
            }
            $html .= '</tr>';
            $i++;
        }
        $html .= '</tbody></table>';
        return $html;
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::current()
     */
    public function current()
    {
        if ($this->header) {
            if (count($this->header) != count($this->currentData)) {
                throw new Error('CSV line has ' . count($this->currentData) . ' fields, but header has ' . count($this->header) . ' fields');
            }
            $data = array_combine($this->header, $this->currentData);
            foreach ($this->formatters as $field => $callback) {
                foreach ($data as $k => &$v) {
                    if (preg_match($field, $k)) {
                        $v = $callback($v);
                    }
                }
            }
            return $data;
        }
        return $this->currentData;
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        rewind($this->fp);
        $this->curLine = 0;
        if ($this->hasHeader) {
            $this->setOption('header', $this->readLine());
        }
        $this->readLine();
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::valid()
     */
    public function valid()
    {
        return (! feof($this->fp));
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::next()
     */
    public function next()
    {
        $this->readLine();
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::key()
     */
    public function key()
    {
        return $this->curLine;
    }
}
