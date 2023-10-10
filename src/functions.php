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
use Norvica\Invoker\Exception\NonInstantiatableClass;
use Norvica\Invoker\Exception\UnresolvedParameterException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;

if (!function_exists('Norvica\Invoker\call')) {
    /**
     * @throws ReflectionException
     */
    function call(callable|array|string $callable, array $arguments, ?Resolver $resolver = null): mixed
    {
        [$parameters, $callable] = prepare($callable, $resolver);
        $canonical = resolve($parameters, $arguments, $resolver);

        return call_user_func_array($callable, $canonical);
    }
}

if (!function_exists('Norvica\Invoker\resolve')) {
    /**
     * @internal
     *
     * @param ReflectionParameter[] $parameters
     * @param array<string, mixed> $arguments
     */
    function resolve(array $parameters, array $arguments = [], ?Resolver $resolver = null): array {
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

        return $canonical;
    }
}

if (!function_exists('Norvica\Invoker\prepare')) {
    /**
     * @internal
     *
     * @return array{0: ReflectionParameter[], 1: Closure}
     * @throws ReflectionException
     */
    function prepare(callable|array|string $callable, ?Resolver $resolver = null): array
    {
        if (is_string($callable)) {
            if (function_exists($callable)) {
                return [
                    (new ReflectionFunction($callable))->getParameters(),
                    $callable(...),
                ];
            }

            if (class_exists($callable)) {
                $instance = instantiate($callable, [], $resolver);

                return [
                    (new ReflectionMethod($callable, '__invoke'))->getParameters(),
                    [$instance, '__invoke'](...)
                ];
            }

            $callable = match(true) {
                str_contains($callable, '::') => explode('::', $callable),
                default => throw new InvalidCallableException("Invalid callable '{$callable}' given."),
            };
        }

        if (is_array($callable)) {
            // object method or class method
            $method = new ReflectionMethod($callable[0], $callable[1]);
            if ($method->isStatic()) {
                return [
                    $method->getParameters(),
                    $callable(...),
                ];
            }

            $instance = is_string($callable[0])
                ? instantiate($callable[0], [], $resolver)
                : $callable[0];

            return [
                $method->getParameters(),
                [$instance, $callable[1]](...),
            ];
        }

        if (is_object($callable) && !$callable instanceof Closure) {
            // object's __invoke method
            return [
                (new ReflectionMethod($callable, '__invoke'))->getParameters(),
                [$callable, '__invoke'](...)
            ];
        }

        // standalone function or closure
        return [
            (new ReflectionFunction($callable))->getParameters(),
            $callable,
        ];
    }
}

if (!function_exists('Norvica\Invoker\instantiate')) {
    /**
     * @throws ReflectionException
     */
    function instantiate(string $class, array $arguments = [], ?Resolver $resolver = null): object {
        $reflection = new ReflectionClass($class);
        if (!$reflection->hasMethod('__construct')) {
            return new $class();
        }

        $constructor = $reflection->getMethod('__construct');
        if (!$constructor->isPublic()) {
            throw new NonInstantiatableClass("Constructor of a class '{$class}' is not public, therefore class cannot be instantiated.");
        }

        $parameters = resolve($constructor->getParameters(), $arguments, $resolver);

        return $reflection->newInstanceArgs($parameters);
    }
}
