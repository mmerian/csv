<?php
namespace Csv;

class Writer extends Reader
{
	protected $mode = 'r+b';

	protected $headerWritten = false;

	protected function init()
	{
		$this->openFile();
		$this->writeHeader();
	}

	protected function writeCsv(array $data)
	{
		if (! $this->headerWritten) {
			$this->headerWritten = true;
			$this->writeHeader();
		}
		return fputcsv($this->fp, $data, $this->delimiter, $this->enclosure);
	}

	protected function writeHeader()
	{
		if ($this->header) {
			$this->writeCsv($this->header);
		}
	}

	public function write(array $data)
	{
		$line = null;
		if ($this->header) {
			foreach ($this->header as $f) {
				$line[$f] = isset($data[$f]) ? $data[$f] : null;
			}
		} else {
			$line = $data;
		}
		return $this->writeCsv($line);
	}
}
