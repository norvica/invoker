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

use Norvica\Invoker\Exception\UnresolvedParameterException;
use ReflectionParameter;

/**
 * A wrapper for multiple resolvers.
 */
final class Resolvers implements Resolver
{
    private array $resolvers;

    public function __construct(Resolver ...$resolvers)
    {
        $this->resolvers = $resolvers;
    }

    /**
     * @throws UnresolvedParameterException
     */
    public function resolve(ReflectionParameter $parameter): mixed
    {
        foreach ($this->resolvers as $resolver) {
            if (!$resolver->supports($parameter)) {
                continue;
            }

            return $resolver->resolve($parameter);
        }

        throw new UnresolvedParameterException($parameter);
    }

    public function supports(ReflectionParameter $parameter): bool
    {
        foreach ($this->resolvers as $resolver) {
            if (!$resolver->supports($parameter)) {
                continue;
            }

            return true;
        }

        return false;
    }
}
