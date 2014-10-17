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
}
