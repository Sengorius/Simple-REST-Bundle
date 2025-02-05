<?php

namespace SkriptManufaktur\SimpleRestBundle\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SkriptManufaktur\SimpleRestBundle\Exception\ApiBusException;
use SkriptManufaktur\SimpleRestBundle\Exception\ApiNotFoundException;
use SkriptManufaktur\SimpleRestBundle\Exception\ApiProcessException;
use SkriptManufaktur\SimpleRestBundle\Exception\PaginationException;
use SkriptManufaktur\SimpleRestBundle\Exception\ValidationException;
use stdClass;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ExceptionTest extends TestCase
{
    public function testApiBusException(): void
    {
        $previousException = new InvalidArgumentException();
        $exception = new ApiBusException('A Test has failed', $previousException);
        $code = 331;

        static::assertInstanceOf(RuntimeException::class, $exception);
        static::assertSame('A Test has failed', $exception->getMessage());
        static::assertSame($code, $exception->getCode());
        static::assertSame($previousException, $exception->getPrevious());
    }

    public function testApiProcessException(): void
    {
        $previousException = new InvalidArgumentException();
        $exception = new ApiProcessException('A Test has failed', $previousException);
        $code = 332;

        static::assertInstanceOf(RuntimeException::class, $exception);
        static::assertSame('A Test has failed', $exception->getMessage());
        static::assertSame($code, $exception->getCode());
        static::assertSame($previousException, $exception->getPrevious());
    }

    public function testApiNotFoundException(): void
    {
        $previousException = new InvalidArgumentException();
        $exception = new ApiNotFoundException('A Test has failed', $previousException);
        $code = 404;

        static::assertInstanceOf(RuntimeException::class, $exception);
        static::assertSame('A Test has failed', $exception->getMessage());
        static::assertSame($code, $exception->getCode());
        static::assertSame($previousException, $exception->getPrevious());
    }

    public function testPaginationException(): void
    {
        $previousException = new InvalidArgumentException();
        $exception = new PaginationException('A Test has failed', $previousException);
        $code = 333;

        static::assertInstanceOf(RuntimeException::class, $exception);
        static::assertSame('A Test has failed', $exception->getMessage());
        static::assertSame($code, $exception->getCode());
        static::assertSame($previousException, $exception->getPrevious());
    }

    public function testValidationException(): void
    {
        $previousException = new InvalidArgumentException();
        $code = 334;
        $entity = new stdClass();
        $entity->id = 122;

        $violationList = new ConstraintViolationList([
            new ConstraintViolation('Name not set', '', [], null, 'name', null),
            new ConstraintViolation('Global error', '', [], null, '', null),
            new ConstraintViolation('Global address error', '', [], null, 'data[addresses]', null),
            new ConstraintViolation('Local address error', '', [], null, 'data[addresses][0]', null),
        ]);
        $exception = new ValidationException($entity, $violationList, $previousException);
        $violations = $exception->getStringifiedViolations();
        $expectedViolations = [
            'root' => [
                'Global error',
            ],
            'name' => [
                'Name not set',
            ],
            'addresses' => [
                'Global address error',
                'Local address error',
            ],
        ];

        static::assertInstanceOf(RuntimeException::class, $exception);
        static::assertSame('Validation for object "stdClass" has failed!', $exception->getMessage());
        static::assertSame($code, $exception->getCode());
        static::assertSame($previousException, $exception->getPrevious());
        static::assertSame('stdClass', $exception->getEntityClass());
        static::assertArrayHasKey('name', $violations);
        static::assertArrayHasKey('root', $violations);
        static::assertCount(1, $violations['name']);
        static::assertSame($expectedViolations, $violations);
    }

    public function testSingleValidationException(): void
    {
        $entity = new stdClass();
        $entity->id = 122;

        $exception = ValidationException::fromSingle(new ConstraintViolation('Global error', '', [], $entity, '', null));
        $violations = $exception->getStringifiedViolations();
        $expectedViolations = [
            'root' => [
                'Global error',
            ],
        ];

        static::assertInstanceOf(RuntimeException::class, $exception);
        static::assertSame('Validation for object "stdClass" has failed!', $exception->getMessage());
        static::assertSame(334, $exception->getCode());
        static::assertSame('stdClass', $exception->getEntityClass());
        static::assertSame($expectedViolations, $violations);
    }
}
