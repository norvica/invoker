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

namespace Norvica\Invoker\Exception;

use ReflectionParameter;
use RuntimeException;

final class UnresolvedParameterException extends RuntimeException
{
    public function __construct(ReflectionParameter $parameter)
    {
        parent::__construct("Cannot resolve parameter '{$parameter->getType()} \${$parameter->getName()}'.");
    }
}
