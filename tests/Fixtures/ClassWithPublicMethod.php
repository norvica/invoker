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

namespace Tests\Norvica\Invoker\Fixtures;

use Closure;
use DateTimeImmutable;
use stdClass;

final class ClassWithPublicMethod
{
    public function __construct(
        public readonly string $foo = 'bar',
    ) {
    }

    public function bar(
        Closure $assertion,
        bool $boolean,
        int $__special,
        float $float,
        int $integer,
        DateTimeImmutable $datetime,
        string $string,
        array $array,
        stdClass $stdClass,
        string $default = '',
        object ...$variadic
    ): void {
        $assertion(
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
        );
    }
}
