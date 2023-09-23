# Invoker

[![Checks](https://github.com/norvica/invoker/actions/workflows/checks.yml/badge.svg)](https://github.com/norvica/invoker/actions/workflows/checks.yml)

**Invoker** is a lightweight PHP library that simplifies invoking functions or methods with named parameters.
It's designed to flexibly resolve missing parameters, utilizing a custom resolver mechanism to provide needed arguments
dynamically.

## Features

- Invoke any callable (function/method) with named parameters.
- Ability to use custom resolvers for dynamic parameter resolution.
- Zero dependency, ready to integrate with your project.

## Installation

Install via Composer:

```bash
composer require norvica/invoker
```

## Basic Usage

Here is a quick example of invoking a simple callable:

```php
// using function
$result = \Norvica\Invoker\call('some_function', ['arg1' => 'value1']);

// using class
$result = \Norvica\Invoker\Invoker::call('some_function', ['arg1' => 'value1']);
```

Sure, let's add some advanced examples and explanations to your `readme.md` based on the `dataProvider`. This will show
potential users the flexibility of your Invoker library.

---

## Advanced Usage

The Invoker library can handle various types of callables including:

- Plain functions
- Closures
- Object instances with an `__invoke()` method
- Object instances with a public method
- Static methods on a class

Here's how you can invoke these using the library:

### Plain Functions

```php
$result = \Norvica\Invoker\call('some_function', ['arg1' => 'value1']);
```

### Closures

```php
$closure = function (string $arg1) {
  // [...]
};
$result = \Norvica\Invoker\call($closure, ['arg1' => 'value1']);
```

### Object with `__invoke` Method

```php
$object = new ClassWithInvokeMethod();
$result = \Norvica\Invoker\call($object, ['arg1' => 'value1']);

// same as
$result = \Norvica\Invoker\call([$object, '__invoke'], ['arg1' => 'value1']);
```

### Object with Public Method

```php
$object = new ClassWithPublicMethod();
$result = \Norvica\Invoker\call([$object, 'someMethod'], ['arg1' => 'value1']);
```

### Class with Static Method

```php
$result = \Norvica\Invoker\call([ClassWithStaticMethod::class, 'someMethod'], ['arg1' => 'value1']);

// or
$result = \Norvica\Invoker\call(ClassWithStaticMethod::class . '::foo', ['arg1' => 'value1']);
```

## Variadic Arguments

The library also supports variadic arguments. Here's how you can pass an array for a variadic parameter.

```php
$closure = function (string $arg1, int ...$number) {
  // [...]
};
$result = \Norvica\Invoker\call($closure, ['arg1' => 'value1', 'number' => [1, 2, 3]]);
```

## Using a Resolver

You can implement your own resolvers by adhering to the `Resolver` interface, which has two methods: `resolve()`
and `supports()`.

### Example: Resolving PSR-11 Container Services

Here's how you can create a simple resolver for a PSR-11 Container:

```php
use Norvica\Invoker\Resolver;
use ReflectionParameter;
use Psr\Container\ContainerInterface;

final class ServiceResolver implements Resolver
{
    public function __construct(
        private ContainerInterface $container,
    ) {}

    public function resolve(ReflectionParameter $parameter): mixed
    {
        return $this->container->get((string) $parameter->getType());
    }

    public function supports(ReflectionParameter $parameter): bool
    {
        return $this->container->has((string) $parameter->getType());
    }
}
```

To invoke a method with this resolver:

```php
$container = // your PSR-11 container
$resolver = new ServiceResolver($container);
$result = \Norvica\Invoker\call([$someObject, 'someMethod'], ['foo' => 'bar'], $resolver);
```

### Example: Resolving PSR-7 Requests

A resolver can be tailored for PSR-7 ServerRequest and Response objects. Here's a sample resolver for handling
a `ServerRequestInterface`.

```php
use Norvica\Invoker\Resolver;
use ReflectionParameter;
use Psr\Http\Message\ServerRequestInterface;

final class RequestResolver implements Resolver
{
    public function __construct(
        private YourRequestFactory $factory,
    ) {}

    public function resolve(ReflectionParameter $parameter): ServerRequestInterface
    {
        return $this->factory->createFromGlobals();
    }

    public function supports(ReflectionParameter $parameter): bool
    {
        return is_a((string) $parameter->getType(), ServerRequestInterface::class, true);
    }
}
```

To use it:

```php
$requestResolver = new RequestResolver($yourRequestFactory);
$result = \Norvica\Invoker\call($someInvokableController, ['id' => '123'], $requestResolver);
```

Of course. Adding that section would clarify the scope and philosophy of the library. Here's how you can include it in
your `readme.md`:

## Running Tests

```bash
./vendor/bin/phpunit --testdox
```

---

## Project Philosophy: Lean and Simple

The primary goal of the **Invoker** library is to remain as lean and straightforward as possible. The focus is on
providing a core utility for invoking callables with named parameters and custom resolvers.

### Out of Scope

While we appreciate suggestions and pull requests, please note that specific implementations of resolvers or extra
functionalities are considered **out of scope** for this library. We aim to keep the codebase clean and easily maintainable,
without incorporating features that may lead to bloat or complexity.

If you require more specialized behaviors, you are encouraged to extend the library or implement your own resolvers
according to your project needs.

---

## Similar Concepts and Alternatives

If you're interested in this library, you might also want to explore other similar tools and frameworks that offer
functionality for argument resolution or method invocation.

### Symfony Action Argument Resolving

One notable alternative
is [Symfony's Action Argument Resolving](https://symfony.com/doc/current/controller/value_resolver.html). This feature
allows you to transform the incoming request or any other data into arguments passed into your controller methods. While
it's more tightly integrated with the Symfony ecosystem, it offers a wide array of built-in resolvers and the ability to
create custom ones.
