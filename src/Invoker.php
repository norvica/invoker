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

namespace Norvica\Invoker;

use ReflectionException;
use function Norvica\Invoker\call;

/**
 * A wrapper for a `call` function, in case you'd like to use it as a static method.
 */
final class Invoker
{
    /**
     * @throws ReflectionException
     */
    public static function call(callable|array $callable, array $arguments, ?Resolver $resolver = null): mixed
    {
        return call($callable, $arguments, $resolver);
    }
}
