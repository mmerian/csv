<?php
namespace Csv;

class WriterTest extends \PHPUnit_Framework_TestCase
{
    public function testCount()
    {
        $w = new Writer(tmpfile(), array(
            'hasHeader' => true
        ));
        $w->write(array(
            'id' => 1,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'birthdate' => '1972-05-22'
        ));
        $w->write(array(
            'id' => 2,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'birthdate' => '1972-05-22'
        ));
        $w->write(array(
            'id' => 3,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'birthdate' => '1972-05-22'
        ));
        $this->assertEquals($w->key(), 3);
    }

    public function testHeader()
    {
        $w = new Writer(tmpfile(), array(
            'hasHeader' => true
        ));
        $w->write(array(
            'id' => 3,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'birthdate' => '1972-05-22'
        ));
        $this->assertEquals($w->getHeader(), array('id', 'first_name', 'last_name', 'birthdate'));
    }
}
