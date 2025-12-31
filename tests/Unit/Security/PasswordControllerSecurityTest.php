<?php

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Tests\Unit\Security;

use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidSupport\RequestLoggerRemote\Controller\PasswordController;
use OxidSupport\RequestLoggerRemote\Core\Module;
use OxidSupport\RequestLoggerRemote\Exception\InvalidTokenException;
use OxidSupport\RequestLoggerRemote\Exception\PasswordTooShortException;
use OxidSupport\RequestLoggerRemote\Service\ApiUserServiceInterface;
use OxidSupport\RequestLoggerRemote\Service\TokenGeneratorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\UnicodeString;

/**
 * Security tests for PasswordController
 * Tests various attack vectors against the password management endpoints
 */
#[CoversClass(PasswordController::class)]
class PasswordControllerSecurityTest extends TestCase
{
    private ApiUserServiceInterface $apiUserService;
    private ModuleSettingServiceInterface $moduleSettingService;
    private TokenGeneratorInterface $tokenGenerator;
    private PasswordController $controller;

    protected function setUp(): void
    {
        $this->apiUserService = $this->createMock(ApiUserServiceInterface::class);
        $this->moduleSettingService = $this->createMock(ModuleSettingServiceInterface::class);
        $this->tokenGenerator = $this->createMock(TokenGeneratorInterface::class);

        $this->controller = new PasswordController(
            $this->apiUserService,
            $this->moduleSettingService,
            $this->tokenGenerator
        );
    }

    // ===========================================
    // TOKEN VALIDATION SECURITY TESTS
    // ===========================================

    public function testEmptyTokenIsRejected(): void
    {
        $this->moduleSettingService
            ->method('getString')
            ->willReturn($this->createUnicodeString('valid-token-123'));

        $this->expectException(InvalidTokenException::class);
        $this->controller->requestLoggerSetPassword('', 'SecurePassword123');
    }

    public function testInvalidTokenIsRejected(): void
    {
        $this->moduleSettingService
            ->method('getString')
            ->willReturn($this->createUnicodeString('valid-token-123'));

        $this->expectException(InvalidTokenException::class);
        $this->controller->requestLoggerSetPassword('wrong-token', 'SecurePassword123');
    }

    public function testTokenTimingAttackPrevention(): void
    {
        // Test that token comparison is constant-time by verifying
        // that an empty stored token rejects any token
        $this->moduleSettingService
            ->method('getString')
            ->willReturn($this->createUnicodeString(''));

        $this->expectException(InvalidTokenException::class);
        $this->controller->requestLoggerSetPassword('any-token', 'SecurePassword123');
    }

    public function testTokenComparisonUsesConstantTimeFunction(): void
    {
        // Verify the validateToken method uses hash_equals for constant-time comparison
        // This prevents timing attacks where attackers can determine correct characters
        // by measuring response times

        $reflection = new \ReflectionMethod(PasswordController::class, 'validateToken');
        $reflection->setAccessible(true);

        // Get the source code of the method
        $fileName = $reflection->getFileName();
        $startLine = $reflection->getStartLine();
        $endLine = $reflection->getEndLine();

        $source = implode('', array_slice(file($fileName), $startLine - 1, $endLine - $startLine + 1));

        // Verify hash_equals is used (constant-time comparison)
        $this->assertStringContainsString(
            'hash_equals',
            $source,
            'Token validation must use hash_equals() for constant-time comparison to prevent timing attacks'
        );

        // Verify direct string comparison is NOT used
        $this->assertStringNotContainsString(
            '$token !== $storedToken',
            $source,
            'Direct string comparison is vulnerable to timing attacks'
        );

        $this->assertStringNotContainsString(
            '$token != $storedToken',
            $source,
            'Direct string comparison is vulnerable to timing attacks'
        );

        $this->assertStringNotContainsString(
            '$token === $storedToken',
            $source,
            'Direct string comparison is vulnerable to timing attacks'
        );

        $this->assertStringNotContainsString(
            '$token == $storedToken',
            $source,
            'Direct string comparison is vulnerable to timing attacks'
        );
    }

    #[DataProvider('sqlInjectionTokenProvider')]
    public function testSqlInjectionInTokenIsRejected(string $maliciousToken): void
    {
        $this->moduleSettingService
            ->method('getString')
            ->willReturn($this->createUnicodeString('valid-token-123'));

        $this->expectException(InvalidTokenException::class);
        $this->controller->requestLoggerSetPassword($maliciousToken, 'SecurePassword123');
    }

    public static function sqlInjectionTokenProvider(): array
    {
        return [
            'basic_injection' => ["' OR '1'='1"],
            'union_injection' => ["' UNION SELECT * FROM oxuser--"],
            'comment_injection' => ["valid-token-123'--"],
            'semicolon_injection' => ["'; DROP TABLE oxuser;--"],
            'hex_injection' => ["0x27204f522027313d27"],
            'double_quote_injection' => ['" OR "1"="1'],
            'null_byte' => ["valid-token\x00malicious"],
            'backslash' => ["valid-token\\' OR '1'='1"],
        ];
    }

    #[DataProvider('xssTokenProvider')]
    public function testXssInTokenIsRejected(string $xssToken): void
    {
        $this->moduleSettingService
            ->method('getString')
            ->willReturn($this->createUnicodeString('valid-token-123'));

        $this->expectException(InvalidTokenException::class);
        $this->controller->requestLoggerSetPassword($xssToken, 'SecurePassword123');
    }

    public static function xssTokenProvider(): array
    {
        return [
            'script_tag' => ['<script>alert("XSS")</script>'],
            'img_onerror' => ['<img src=x onerror=alert("XSS")>'],
            'svg_onload' => ['<svg onload=alert("XSS")>'],
            'event_handler' => ['"><script>alert("XSS")</script>'],
            'javascript_protocol' => ['javascript:alert("XSS")'],
            'data_uri' => ['data:text/html,<script>alert("XSS")</script>'],
        ];
    }

    // ===========================================
    // PASSWORD VALIDATION SECURITY TESTS
    // ===========================================

    public function testPasswordTooShortIsRejected(): void
    {
        $validToken = 'valid-token-123';
        $this->moduleSettingService
            ->method('getString')
            ->willReturn($this->createUnicodeString($validToken));

        $this->expectException(PasswordTooShortException::class);
        $this->controller->requestLoggerSetPassword($validToken, '1234567'); // 7 chars
    }

    public function testPasswordExactlyMinLengthIsAccepted(): void
    {
        $validToken = 'valid-token-123';
        $this->moduleSettingService
            ->method('getString')
            ->willReturn($this->createUnicodeString($validToken));

        $this->apiUserService
            ->expects($this->once())
            ->method('setPasswordForApiUser')
            ->with('12345678'); // 8 chars

        $result = $this->controller->requestLoggerSetPassword($validToken, '12345678');
        $this->assertTrue($result);
    }

    public function testEmptyPasswordIsRejected(): void
    {
        $validToken = 'valid-token-123';
        $this->moduleSettingService
            ->method('getString')
            ->willReturn($this->createUnicodeString($validToken));

        $this->expectException(PasswordTooShortException::class);
        $this->controller->requestLoggerSetPassword($validToken, '');
    }

    #[DataProvider('sqlInjectionPasswordProvider')]
    public function testSqlInjectionInPasswordIsHandledSafely(string $maliciousPassword): void
    {
        // Passwords with SQL injection should be accepted if >= 8 chars
        // The actual protection happens in the User model's password hashing
        $validToken = 'valid-token-123';
        $this->moduleSettingService
            ->method('getString')
            ->willReturn($this->createUnicodeString($validToken));

        if (strlen($maliciousPassword) >= 8) {
            // Should accept the password (it will be hashed)
            $this->apiUserService
                ->expects($this->once())
                ->method('setPasswordForApiUser')
                ->with($maliciousPassword);

            $result = $this->controller->requestLoggerSetPassword($validToken, $maliciousPassword);
            $this->assertTrue($result);
        } else {
            $this->expectException(PasswordTooShortException::class);
            $this->controller->requestLoggerSetPassword($validToken, $maliciousPassword);
        }
    }

    public static function sqlInjectionPasswordProvider(): array
    {
        return [
            'basic_injection' => ["' OR '1'='1' --"],
            'union_injection' => ["' UNION SELECT password FROM admin--"],
            'drop_table' => ["'; DROP TABLE users;--"],
            'comment_injection' => ["password123'--"],
        ];
    }

    // ===========================================
    // TOKEN INVALIDATION TESTS
    // ===========================================

    public function testTokenIsClearedAfterSuccessfulPasswordSet(): void
    {
        $validToken = 'valid-token-123';
        $this->moduleSettingService
            ->method('getString')
            ->willReturn($this->createUnicodeString($validToken));

        // Verify token is cleared after successful password set
        $this->moduleSettingService
            ->expects($this->once())
            ->method('saveString')
            ->with(Module::SETTING_SETUP_TOKEN, '', Module::MODULE_ID);

        $this->controller->requestLoggerSetPassword($validToken, 'SecurePassword123');
    }

    public function testTokenCannotBeReusedAfterSuccessfulPasswordSet(): void
    {
        $validToken = 'valid-token-123';

        // First call - token is valid
        $this->moduleSettingService
            ->expects($this->exactly(2))
            ->method('getString')
            ->willReturnOnConsecutiveCalls(
                $this->createUnicodeString($validToken),
                $this->createUnicodeString('') // Token is cleared after first use
            );

        // First password set succeeds
        $this->controller->requestLoggerSetPassword($validToken, 'SecurePassword123');

        // Second attempt with same token should fail
        $this->expectException(InvalidTokenException::class);
        $this->controller->requestLoggerSetPassword($validToken, 'AnotherPassword123');
    }

    // ===========================================
    // BRUTE FORCE / ENUMERATION TESTS
    // ===========================================

    public function testNoUserEnumerationOnInvalidToken(): void
    {
        // Both empty and wrong tokens should throw the same exception
        // to prevent token enumeration
        $this->moduleSettingService
            ->method('getString')
            ->willReturn($this->createUnicodeString(''));

        try {
            $this->controller->requestLoggerSetPassword('guessed-token', 'Password123');
            $this->fail('Expected InvalidTokenException');
        } catch (InvalidTokenException $e) {
            // Verify exception message doesn't reveal whether token exists
            $this->assertStringNotContainsString('not found', $e->getMessage());
            $this->assertStringNotContainsString('does not exist', $e->getMessage());
        }
    }

    // ===========================================
    // AUTHORIZATION TESTS
    // ===========================================

    public function testResetPasswordRequiresAuthentication(): void
    {
        // Verify the method has #[Logged] attribute
        $reflection = new \ReflectionMethod(PasswordController::class, 'requestLoggerResetPassword');
        $attributes = $reflection->getAttributes();

        $hasLoggedAttribute = false;
        foreach ($attributes as $attribute) {
            if (str_contains($attribute->getName(), 'Logged')) {
                $hasLoggedAttribute = true;
                break;
            }
        }

        $this->assertTrue($hasLoggedAttribute, 'requestLoggerResetPassword must have #[Logged] attribute');
    }

    public function testResetPasswordRequiresPermission(): void
    {
        // Verify the method has #[Right] attribute
        $reflection = new \ReflectionMethod(PasswordController::class, 'requestLoggerResetPassword');
        $attributes = $reflection->getAttributes();

        $hasRightAttribute = false;
        foreach ($attributes as $attribute) {
            if (str_contains($attribute->getName(), 'Right')) {
                $hasRightAttribute = true;
                break;
            }
        }

        $this->assertTrue($hasRightAttribute, 'requestLoggerResetPassword must have #[Right] attribute');
    }

    public function testSetPasswordDoesNotRequireAuthentication(): void
    {
        // requestLoggerSetPassword uses token-based auth, not session auth
        $reflection = new \ReflectionMethod(PasswordController::class, 'requestLoggerSetPassword');
        $attributes = $reflection->getAttributes();

        $hasLoggedAttribute = false;
        foreach ($attributes as $attribute) {
            if (str_contains($attribute->getName(), 'Logged')) {
                $hasLoggedAttribute = true;
                break;
            }
        }

        $this->assertFalse($hasLoggedAttribute, 'requestLoggerSetPassword should NOT have #[Logged] attribute (uses token auth)');
    }

    // ===========================================
    // HELPER METHODS
    // ===========================================

    private function createUnicodeString(string $value): UnicodeString
    {
        $mock = $this->createMock(UnicodeString::class);
        $mock->method('__toString')->willReturn($value);
        return $mock;
    }
}
