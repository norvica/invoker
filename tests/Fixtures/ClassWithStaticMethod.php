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

final class ClassWithStaticMethod
{
    public static function foo(
        Closure $assertion,
        DateTimeImmutable $datetime,
        bool $boolean,
        float $float,
        int $integer,
        string $string,
        int $__special,
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
