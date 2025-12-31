<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Tests\Unit\DataType;

use OxidSupport\RequestLoggerRemote\DataType\SettingType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SettingType::class)]
final class SettingTypeTest extends TestCase
{
    public function testConstructorSetsName(): void
    {
        $setting = new SettingType('testName', 'string');

        $this->assertEquals('testName', $setting->getName());
    }

    public function testConstructorSetsType(): void
    {
        $setting = new SettingType('testName', 'boolean');

        $this->assertEquals('boolean', $setting->getType());
    }

    public function testConstructorSetsDefaultSupportedToTrue(): void
    {
        $setting = new SettingType('testName', 'string');

        $this->assertTrue($setting->isSupported());
    }

    public function testConstructorCanSetSupportedToTrue(): void
    {
        $setting = new SettingType('testName', 'string', true);

        $this->assertTrue($setting->isSupported());
    }

    public function testConstructorCanSetSupportedToFalse(): void
    {
        $setting = new SettingType('testName', 'string', false);

        $this->assertFalse($setting->isSupported());
    }

    public function testGetNameReturnsString(): void
    {
        $setting = new SettingType('testName', 'string');

        $this->assertIsString($setting->getName());
    }

    public function testGetTypeReturnsString(): void
    {
        $setting = new SettingType('testName', 'boolean');

        $this->assertIsString($setting->getType());
    }

    public function testIsSupportedReturnsBool(): void
    {
        $setting = new SettingType('testName', 'string');

        $this->assertIsBool($setting->isSupported());
    }

    public function testCanCreateWithEmptyName(): void
    {
        $setting = new SettingType('', 'string');

        $this->assertEquals('', $setting->getName());
    }

    public function testCanCreateWithEmptyType(): void
    {
        $setting = new SettingType('testName', '');

        $this->assertEquals('', $setting->getType());
    }

    public function testClassIsFinal(): void
    {
        $reflection = new \ReflectionClass(SettingType::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function testHasTypeAttribute(): void
    {
        $reflection = new \ReflectionClass(SettingType::class);
        $attributes = $reflection->getAttributes();

        $hasTypeAttribute = false;
        foreach ($attributes as $attribute) {
            if (str_contains($attribute->getName(), 'Type')) {
                $hasTypeAttribute = true;
                break;
            }
        }

        $this->assertTrue($hasTypeAttribute, 'SettingType should have GraphQLite Type attribute');
    }

    public function testGetNameHasFieldAttribute(): void
    {
        $reflection = new \ReflectionClass(SettingType::class);
        $method = $reflection->getMethod('getName');
        $attributes = $method->getAttributes();

        $hasFieldAttribute = false;
        foreach ($attributes as $attribute) {
            if (str_contains($attribute->getName(), 'Field')) {
                $hasFieldAttribute = true;
                break;
            }
        }

        $this->assertTrue($hasFieldAttribute, 'getName should have GraphQLite Field attribute');
    }

    public function testGetTypeHasFieldAttribute(): void
    {
        $reflection = new \ReflectionClass(SettingType::class);
        $method = $reflection->getMethod('getType');
        $attributes = $method->getAttributes();

        $hasFieldAttribute = false;
        foreach ($attributes as $attribute) {
            if (str_contains($attribute->getName(), 'Field')) {
                $hasFieldAttribute = true;
                break;
            }
        }

        $this->assertTrue($hasFieldAttribute, 'getType should have GraphQLite Field attribute');
    }

    public function testIsSupportedHasFieldAttribute(): void
    {
        $reflection = new \ReflectionClass(SettingType::class);
        $method = $reflection->getMethod('isSupported');
        $attributes = $method->getAttributes();

        $hasFieldAttribute = false;
        foreach ($attributes as $attribute) {
            if (str_contains($attribute->getName(), 'Field')) {
                $hasFieldAttribute = true;
                break;
            }
        }

        $this->assertTrue($hasFieldAttribute, 'isSupported should have GraphQLite Field attribute');
    }

    public function testAllPropertiesArePrivate(): void
    {
        $reflection = new \ReflectionClass(SettingType::class);
        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            $this->assertTrue($property->isPrivate(), "Property {$property->getName()} should be private");
        }
    }

    public function testHasExactlyThreeProperties(): void
    {
        $reflection = new \ReflectionClass(SettingType::class);
        $properties = $reflection->getProperties();

        $this->assertCount(3, $properties);
    }
}
