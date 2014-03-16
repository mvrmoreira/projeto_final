<?php

/*
#run all tests in the Utility directory
$ phpunit -c app src/Acme/DemoBundle/Tests/Utility/

# run tests for the Calculator class
$ phpunit -c app src/Acme/DemoBundle/Tests/Utility/CalculatorTest.php

# run all tests for the entire Bundle
$ phpunit -c app src/Acme/DemoBundle/
 */

namespace Acme\DemoBundle\Tests\Utility;

use Acme\DemoBundle\Utility\Calculator;

class CalculatorTest extends \PHPUnit_Framework_TestCase
{
    public function testAdd()
    {
        $calc = new Calculator();
        $result = $calc->add(30, 12);

        // assert that your calculator added the numbers correctly!
        $this->assertEquals(42, $result);
    }
}