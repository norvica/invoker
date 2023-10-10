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

final class ClassWithInvokeMethod
{
    public function __construct(
        public DateTimeImmutable $timestamp,
    ) {
    }

    public function __invoke(
        Closure $assertion,
        int $__special,
        bool $boolean,
        DateTimeImmutable $datetime,
        float $float,
        int $integer,
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
