<?php

namespace Csv;

class Writer extends Reader
{
    protected $mode = 'a+b';

    protected $headerWritten = false;

    protected function init()
    {
        $this->openFile();
        $this->writeHeader();
    }

    protected function writeCsv(array $data)
    {
        if (!$this->headerWritten) {
            if ($this->hasHeader && (!$this->header)) {
                $this->header = array_keys($data);
            }
            $this->writeHeader();
        }

        if ($this->inputEncoding != $this->outputEncoding) {
            $data = array_map(function ($str) {
                return mb_convert_encoding(strval($str), $this->outputEncoding, $this->inputEncoding);
            }, $data);
        } else {
            $data = array_map(function ($str) {
                return strval($str);
            }, $data);
        }

        return fputcsv($this->fp, $data, $this->delimiter, $this->enclosure);
    }

    protected function writeHeader()
    {
        if ($this->header && (!$this->headerWritten)) {
            /*
             * headerWritten must be set before
             * calling writeCsv(), since writeCsv()
             * checks it.
             */
            $this->headerWritten = true;
            $this->writeCsv($this->header);
        }

        return $this;
    }

    public function write(array $data)
    {
        $line = null;
        if ($this->header) {
            $line = array();
            foreach ($this->header as $i => $f) {
                if (isset($data[$f])) {
                    $line[$f] = $data[$f];
                } elseif (isset($data[$i])) {
                    $line[$f] = $data[$i];
                } else {
                    $line[$f] = null;
                }
            }
        } else {
            $line = $data;
        }

        ++$this->curLine;

        return $this->writeCsv($line);
    }
}
