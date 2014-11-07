<?php
namespace Csv;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    protected $reader;

    protected function setUp()
    {
        $this->reader= new Reader(dirname(__DIR__) . '/sample-data/us-500.csv', array(
            'hasHeader' => true,
            'delimiter' => ',',
            'inputEncoding' => 'ISO-8859-15'
        ));
    }

    public function testHeader()
    {
        $expectedHeader = array (
            'first_name',
            'last_name',
            'company_name',
            'address',
            'city',
            'county',
            'state',
            'zip',
            'phone1',
            'phone2',
            'email',
            'web'
        );
        $this->assertEquals($this->reader->getHeader(), $expectedHeader);
    }

    public function testLineCount()
    {
        $this->assertEquals($this->reader->key(), 2);
        $i = 0;
        foreach ($this->reader as $line) {
            $i++;
            if (5 == $i) {
                break;
            }
        }
        $this->assertEquals($this->reader->key(), 6);
    }

    public function testFormatter()
    {
        $this->reader->registerFormatter('first_name', function($str) {
            return strtoupper($str);
        });
        $this->reader->registerFormatter('/^phone.*/', function($str) {
            return str_replace('-', '', $str);
        });
        $line = $this->reader->current();

        $this->assertEquals($line['first_name'], 'JAMES');
        $this->assertEquals($line['phone1'], '5046218927');
        $this->assertEquals($line['phone2'], '5048451427');
    }

    public function testIterator()
    {
        $line = null;
        while ($line = $this->reader->fetch()) {

        }
        $this->assertEquals($line, false);
    }

    public function testBlankLines()
    {
        $this->reader= new Reader(dirname(__DIR__) . '/sample-data/blank-lines.csv', array(
            'hasHeader' => true,
            'delimiter' => ';',
            'inputEncoding' => 'ISO-8859-15'
        ));
        while ($line = $this->reader->fetch()) {

        }
    }

    /**
     * @expectedException \Csv\Error
     */
    public function testBlankLinesException()
    {
        $this->reader= new Reader(dirname(__DIR__) . '/sample-data/blank-lines.csv', array(
            'hasHeader' => true,
            'delimiter' => ';',
            'inputEncoding' => 'ISO-8859-15',
            'ignoreBlankLines' => false
        ));
        while ($line = $this->reader->fetch()) {

        }
    }
}
