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

use Closure;
use Norvica\Invoker\Exception\InvalidCallableException;
use Norvica\Invoker\Exception\UnresolvedParameterException;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use TypeError;

if (!function_exists('Norvica\Invoker\call')) {
    /**
     * @throws ReflectionException
     */
    function call(callable|array|string $callable, array $arguments, ?Resolver $resolver = null): mixed
    {
        $reflection = reflection($callable);
        $parameters = $reflection->getParameters();
        $canonical = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();

            if (array_key_exists($name, $arguments)) {
                if ($parameter->isVariadic()) {
                    $canonical = array_merge($canonical, $arguments[$name]);
                    break;
                }

                $canonical[] = $arguments[$name];
            } elseif (null !== $resolver && $resolver->supports($parameter)) {
                $canonical[] = $resolver->resolve($parameter);
            } elseif ($parameter->isDefaultValueAvailable()) {
                $canonical[] = $parameter->getDefaultValue();
            } else {
                throw new UnresolvedParameterException($parameter);
            }
        }

        return call_user_func_array($callable, $canonical);
    }
}

if (!function_exists('Norvica\Invoker\reflection')) {
    /**
     * @internal
     * @throws ReflectionException
     */
    function reflection(callable|array|string $callable): ReflectionMethod|ReflectionFunction
    {
        if (is_string($callable)) {
            if (function_exists($callable)) {
                return new ReflectionFunction($callable);
            }

            $callable = match(true) {
                str_contains($callable, '::') => explode('::', $callable),
                default => throw new InvalidCallableException("Invalid callable '{$callable}' given."),
            };
        }

        if (is_array($callable)) {
            // object method or class method
            return new ReflectionMethod($callable[0], $callable[1]);
        }

        if (is_object($callable) && !$callable instanceof Closure) {
            // object's __invoke method
            return new ReflectionMethod($callable, '__invoke');
        }

        // standalone function or closure
        return new ReflectionFunction($callable);
    }
}
