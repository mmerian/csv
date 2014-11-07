<?php
namespace Csv;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testOpenEmpty()
    {
        $reader = new Reader(dirname(__DIR__) . '/sample-data/us-500.csv', array(
            'hasHeader' => true,
            'delimiter' => ',',
            'inputEncoding' => 'ISO-8859-15'
        ));
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
        $this->assertEquals($reader->getHeader(), $expectedHeader);
    }

    public function testLineCount()
    {
        $reader = new Reader(dirname(__DIR__) . '/sample-data/us-500.csv', array(
            'hasHeader' => true,
            'delimiter' => ',',
            'inputEncoding' => 'ISO-8859-15'
        ));
        $this->assertEquals($reader->key(), 2);
        $i = 0;
        foreach ($reader as $line) {
            $i++;
            if (5 == $i) {
                break;
            }
        }
        $this->assertEquals($reader->key(), 6);
    }

    public function testFormatter()
    {
        $reader = new Reader(dirname(__DIR__) . '/sample-data/us-500.csv', array(
            'hasHeader' => true,
            'delimiter' => ',',
            'inputEncoding' => 'ISO-8859-15'
        ));
        $reader->registerFormatter('first_name', function($str) {
            return strtoupper($str);
        });
        $reader->registerFormatter('/^phone.*/', function($str) {
            return str_replace('-', '', $str);
        });
        $line = $reader->current();

        $this->assertEquals($line['first_name'], 'JAMES');
        $this->assertEquals($line['phone1'], '5046218927');
        $this->assertEquals($line['phone2'], '5048451427');
    }
}
