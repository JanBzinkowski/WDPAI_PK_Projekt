<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Container;
use PHPUnit\Framework\TestCase;

class Dependency
{
    public string $value = 'active';
}

class ServiceWithDependency
{
    public Dependency $dependency;

    public function __construct(Dependency $dependency)
    {
        $this->dependency = $dependency;
    }
}

final class ContainerTest extends TestCase
{
    public function testResolvesClassWithoutBindings(): void
    {
        $container = new Container();
        $instance = $container->get(Dependency::class);

        $this->assertInstanceOf(Dependency::class, $instance);
        $this->assertSame('active', $instance->value);
    }

    public function testAutowiresConstructorDependencies(): void
    {
        $container = new Container();
        /** @var ServiceWithDependency $service */
        $service = $container->get(ServiceWithDependency::class);

        $this->assertInstanceOf(ServiceWithDependency::class, $service);
        $this->assertInstanceOf(Dependency::class, $service->dependency);
    }

    public function testSingletonReturnsSameInstance(): void
    {
        $container = new Container();
        $container->singleton(Dependency::class, fn () => new Dependency());

        $first = $container->get(Dependency::class);
        $second = $container->get(Dependency::class);

        $this->assertSame($first, $second);
    }

    public function testInstanceRegistration(): void
    {
        $container = new Container();
        $custom = new Dependency();
        $custom->value = 'custom-value';

        $container->instance(Dependency::class, $custom);

        $resolved = $container->get(Dependency::class);
        $this->assertSame($custom, $resolved);
        $this->assertSame('custom-value', $resolved->value);
    }
}
