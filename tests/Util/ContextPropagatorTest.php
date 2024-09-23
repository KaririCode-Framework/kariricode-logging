<?php

declare(strict_types=1);

namespace KaririCode\Logging\Tests\Util;

use KaririCode\Logging\Util\ContextPropagator;
use PHPUnit\Framework\TestCase;

final class ContextPropagatorTest extends TestCase
{
    public function testSetGetContext(): void
    {
        ContextPropagator::set('key', 'value');
        $this->assertEquals('value', ContextPropagator::get('key'));
    }

    public function testRemoveContext(): void
    {
        ContextPropagator::set('key', 'value');
        ContextPropagator::remove('key');
        $this->assertNull(ContextPropagator::get('key'));
    }

    public function testClearContext(): void
    {
        ContextPropagator::set('key', 'value');
        ContextPropagator::clear();
        $this->assertEmpty(ContextPropagator::all());
    }
}
