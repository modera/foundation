<?php

namespace Modera\ServerCrudBundle\Tests\Unit\ExceptionHandling;

use Modera\ServerCrudBundle\ExceptionHandling\BypassExceptionHandler;

class BypassExceptionHandlerTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateResponse(): void
    {
        $e = new \Exception('Foo');

        $bypassHandler = new BypassExceptionHandler();

        $thrownException = null;
        try {
            $bypassHandler->createResponse($e, 'test');
        } catch (\Exception $e) {
            $thrownException = $e;
        }

        $this->assertEquals('Exception', \get_class($thrownException));
        $this->assertEquals('Foo', $thrownException->getMessage());
    }
}
