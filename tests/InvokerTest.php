<?php

declare(strict_types=1);

/*
 * This file is part of norvica/invoker.
 *
 * (c) Siarhei Kvashnin <serge@norvica.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Norvica\Invoker;

use Closure;
use DateTimeImmutable;
use Norvica\Invoker\Resolver;
use Norvica\Invoker\Resolvers;
use ReflectionParameter;
use Tests\Norvica\Invoker\Fixtures\ClassWithInvokeMethod;
use Tests\Norvica\Invoker\Fixtures\ClassWithPublicMethod;
use Tests\Norvica\Invoker\Fixtures\ClassWithStaticMethod;
use Generator;
use PHPUnit\Framework\TestCase;
use stdClass;
use function Norvica\Invoker\call;

final class InvokerTest extends TestCase
{
    public static function callableProvider(): Generator
    {
        $resolver = new Resolvers(
            new class() implements Resolver {
                public function resolve(ReflectionParameter $parameter): DateTimeImmutable
                {
                    return new DateTimeImmutable('1945-07-22');
                }

                public function supports(ReflectionParameter $parameter): bool
                {
                    return ((string) $parameter->getType()) === DateTimeImmutable::class;
                }
            },
            new class() implements Resolver {
                public function resolve(ReflectionParameter $parameter): int
                {
                    return PHP_INT_MAX;
                }

                public function supports(ReflectionParameter $parameter): bool
                {
                    return str_starts_with($parameter->getName(), '__');
                }
            },
        );

        yield 'function' => ['Tests\Norvica\Invoker\Fixtures\some_function', $resolver];
        yield 'closure' => [static fn(
            Closure $assertion,
            int $integer,
            bool $boolean,
            DateTimeImmutable $datetime,
            int $__special,
            float $float,
            stdClass $stdClass,
            string $string,
            array $array,
            string $default = '',
            object ...$variadic
        ) => $assertion(
            $boolean,
            $float,
            $integer,
            $string,
            $array,
            $stdClass,
            $datetime,
            $__special,
            $default,
            ...$variadic,
        ), $resolver];
        yield 'class instance with __invoke method' => [new ClassWithInvokeMethod(new DateTimeImmutable()), $resolver];
        yield 'non-instantiated class with __invoke method' => [ClassWithInvokeMethod::class, $resolver];
        yield 'class instance with public method' => [[new ClassWithPublicMethod(), 'bar'], $resolver];
        yield 'non-instantiated class with public method' => [[ClassWithPublicMethod::class, 'bar'], $resolver];
        yield 'non-instantiated class with public method (alternative notation)' => [ClassWithPublicMethod::class . '::bar', $resolver];
        yield 'class with static method' => [[ClassWithStaticMethod::class, 'foo'], $resolver];
        yield 'class with static method (alternative notation)' => [ClassWithStaticMethod::class . '::foo', $resolver];
    }

    /**
     * @dataProvider callableProvider
     */
    public function testCall(callable|array|string $callable, Resolver $resolvers): void
    {
        $parameters = [
            'variadic' => [(object) ['foo' => 'bar'], (object) ['bar' => 'foo']],
            'stdClass' => (object) ['a' => 'b'],
            'array' => ['bar'],
            'string' => 'foo',
            'integer' => 1024,
            'float' => 3.14,
            'boolean' => false,
            'assertion' => $this->assertion(),
        ];

        call($callable, $parameters, $resolvers);
    }

    private function assertion(): Closure
    {
        return function(
            bool $boolean,
            float $float,
            int $integer,
            string $string,
            array $array,
            stdClass $stdClass,
            DateTimeImmutable $datetime,
            int $__special,
            string $default = '',
            object ...$variadic,
        ):void {
            $this->assertFalse($boolean);
            $this->assertEquals(3.14, $float);
            $this->assertEquals(1024, $integer);
            $this->assertEquals('foo', $string);
            $this->assertEquals(['bar'], $array);
            $this->assertEquals('b', $stdClass->a);
            $this->assertEquals('bar', $variadic[0]->foo);
            $this->assertEquals('foo', $variadic[1]->bar);
            $this->assertEquals('1945-07-22', $datetime->format('Y-m-d'));
            $this->assertEquals(PHP_INT_MAX, $__special);
        };
    }
}
