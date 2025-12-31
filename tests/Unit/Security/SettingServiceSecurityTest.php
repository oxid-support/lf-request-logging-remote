<?php

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Tests\Unit\Security;

use OxidSupport\RequestLogger\Shop\Compatibility\ModuleSettings\ModuleSettingsPort;
use OxidSupport\RequestLoggerRemote\Exception\InvalidCollectionException;
use OxidSupport\RequestLoggerRemote\Service\SettingService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Security tests for SettingService
 * Tests various attack vectors against the settings management
 */
#[CoversClass(SettingService::class)]
class SettingServiceSecurityTest extends TestCase
{
    private ModuleSettingsPort $moduleSettingsPort;
    private SettingService $service;

    protected function setUp(): void
    {
        $this->moduleSettingsPort = $this->createMock(ModuleSettingsPort::class);
        $this->service = new SettingService($this->moduleSettingsPort);
    }

    // ===========================================
    // JSON INJECTION TESTS
    // ===========================================

    public function testInvalidJsonIsRejected(): void
    {
        $this->expectException(InvalidCollectionException::class);
        $this->service->setRedactItems('not valid json');
    }

    public function testJsonStringInsteadOfArrayIsRejected(): void
    {
        $this->expectException(InvalidCollectionException::class);
        $this->service->setRedactItems('"just a string"');
    }

    public function testJsonNumberInsteadOfArrayIsRejected(): void
    {
        $this->expectException(InvalidCollectionException::class);
        $this->service->setRedactItems('12345');
    }

    public function testJsonNullIsRejected(): void
    {
        $this->expectException(InvalidCollectionException::class);
        $this->service->setRedactItems('null');
    }

    public function testJsonBooleanIsRejected(): void
    {
        $this->expectException(InvalidCollectionException::class);
        $this->service->setRedactItems('true');
    }

    public function testValidJsonArrayIsAccepted(): void
    {
        $this->moduleSettingsPort
            ->expects($this->once())
            ->method('saveCollection');

        $this->moduleSettingsPort
            ->method('getCollection')
            ->willReturn(['password', 'token']);

        $result = $this->service->setRedactItems('["password", "token"]');
        $this->assertJson($result);
    }

    public function testEmptyJsonArrayIsAccepted(): void
    {
        $this->moduleSettingsPort
            ->expects($this->once())
            ->method('saveCollection')
            ->with(
                $this->anything(),
                [],
                $this->anything()
            );

        $this->moduleSettingsPort
            ->method('getCollection')
            ->willReturn([]);

        $result = $this->service->setRedactItems('[]');
        $this->assertEquals('[]', $result);
    }

    #[DataProvider('maliciousJsonProvider')]
    public function testMaliciousJsonInputs(string $maliciousJson, bool $shouldReject): void
    {
        if ($shouldReject) {
            $this->expectException(InvalidCollectionException::class);
            $this->service->setRedactItems($maliciousJson);
        } else {
            // If it's valid JSON array, it should be accepted
            // The data itself is just stored, not executed
            $this->moduleSettingsPort
                ->expects($this->once())
                ->method('saveCollection');

            $decoded = json_decode($maliciousJson, true);
            $this->moduleSettingsPort
                ->method('getCollection')
                ->willReturn($decoded);

            $result = $this->service->setRedactItems($maliciousJson);
            $this->assertIsString($result);
        }
    }

    public static function maliciousJsonProvider(): array
    {
        return [
            'xss_in_array' => ['["<script>alert(1)</script>"]', false], // Stored, not executed
            'sql_in_array' => ['["\'OR 1=1--"]', false], // Stored, not executed
            'deeply_nested' => ['[[[[[[[[[[]]]]]]]]]]', false], // Valid array
            'object_not_array' => ['{"key": "value"}', true], // Object rejected
            'mixed_types' => ['["string", 123, true, null]', false], // Valid array
            'unicode_escape' => ['["\\u003cscript\\u003e"]', false], // Unicode is fine
            'empty_key_object' => ['{"": "value"}', true], // Object rejected
            'prototype_pollution' => ['{"__proto__": {"admin": true}}', true], // Object rejected
            'constructor_pollution' => ['{"constructor": {"prototype": {}}}', true], // Object rejected
        ];
    }

    // ===========================================
    // LOG LEVEL INJECTION TESTS
    // ===========================================

    #[DataProvider('maliciousLogLevelProvider')]
    public function testMaliciousLogLevelInputs(string $maliciousLogLevel): void
    {
        // Log level is just saved as string, no execution
        // The actual validation happens in the log framework
        $this->moduleSettingsPort
            ->expects($this->once())
            ->method('saveString')
            ->with(
                $this->anything(),
                $maliciousLogLevel,
                $this->anything()
            );

        $this->moduleSettingsPort
            ->method('getString')
            ->willReturn($maliciousLogLevel);

        $result = $this->service->setLogLevel($maliciousLogLevel);
        $this->assertSame($maliciousLogLevel, $result);
    }

    public static function maliciousLogLevelProvider(): array
    {
        return [
            'sql_injection' => ["' OR '1'='1"],
            'xss_attempt' => ['<script>alert(1)</script>'],
            'command_injection' => ['debug; rm -rf /'],
            'path_traversal' => ['../../../etc/passwd'],
            'null_byte' => ["debug\x00malicious"],
            'very_long_string' => [str_repeat('a', 10000)],
        ];
    }

    // ===========================================
    // BOOLEAN INJECTION TESTS
    // ===========================================

    public function testBooleanSettingsOnlyAcceptBooleans(): void
    {
        // PHP type system enforces boolean type
        // These tests verify the interface
        $this->moduleSettingsPort
            ->expects($this->once())
            ->method('saveBoolean')
            ->with(
                $this->anything(),
                true,
                $this->anything()
            );

        $this->moduleSettingsPort
            ->method('getBoolean')
            ->willReturn(true);

        $result = $this->service->setLogFrontendEnabled(true);
        $this->assertTrue($result);
    }

    // ===========================================
    // DATA EXPOSURE TESTS
    // ===========================================

    public function testGetRedactItemsReturnsValidJson(): void
    {
        $sensitiveData = ['password', 'credit_card', 'ssn'];

        $this->moduleSettingsPort
            ->method('getCollection')
            ->willReturn($sensitiveData);

        $result = $this->service->getRedactItems();

        // Verify output is valid JSON
        $this->assertJson($result);

        // Verify no PHP serialization is used (security risk)
        $this->assertStringNotContainsString('O:', $result); // No object serialization
        $this->assertStringNotContainsString('a:', $result); // No array serialization (PHP format)
    }

    public function testGetAllSettingsDoesNotExposeValues(): void
    {
        // getAllSettings should only return setting names and types, not values
        $settings = $this->service->getAllSettings();

        foreach ($settings as $setting) {
            // Verify no actual values are exposed
            $this->assertObjectNotHasProperty('value', $setting);
        }
    }
}
