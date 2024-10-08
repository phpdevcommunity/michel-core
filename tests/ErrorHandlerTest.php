<?php

namespace Test\PhpDevCommunity\Michel\Core;

use PhpDevCommunity\Michel\Core\ErrorHandler\ErrorHandler;
use PHPUnit\Framework\TestCase;

class ErrorHandlerTest extends TestCase
{
    public function testDeprecationErrors()
    {
        $errorHandler = new ErrorHandler();
        $errorHandler->__invoke(E_USER_DEPRECATED, 'This is a deprecated error',__FILE__);

        $deprecations = $errorHandler->getDeprecations();

        $this->assertCount(1, $deprecations);

        $deprecation = $deprecations[0];
        $this->assertArrayHasKey('level', $deprecation);
        $this->assertEquals(E_USER_DEPRECATED, $deprecation['level']);
        $this->assertArrayHasKey('message', $deprecation);
        $this->assertEquals('This is a deprecated error', $deprecation['message']);
    }
}
