<?php

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Tests\Unit\Security;

use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidSupport\RequestLoggerRemote\Core\Module;
use OxidSupport\RequestLoggerRemote\Service\ApiUserService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Security tests for ApiUserService
 * Tests SQL injection protection and secure password handling
 */
#[CoversClass(ApiUserService::class)]
class ApiUserServiceSecurityTest extends TestCase
{
    private QueryBuilderFactoryInterface $queryBuilderFactory;

    protected function setUp(): void
    {
        $this->queryBuilderFactory = $this->createMock(QueryBuilderFactoryInterface::class);
    }

    // ===========================================
    // SQL INJECTION PREVENTION TESTS
    // ===========================================

    public function testLoadApiUserUsesParameterizedQuery(): void
    {
        // Verify that the email is passed as a parameter, not concatenated
        $queryBuilder = $this->createMock(\Doctrine\DBAL\Query\QueryBuilder::class);

        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('OXID')
            ->willReturnSelf();

        $queryBuilder->expects($this->once())
            ->method('from')
            ->with('oxuser')
            ->willReturnSelf();

        $queryBuilder->expects($this->once())
            ->method('where')
            ->with('OXUSERNAME = :email')
            ->willReturnSelf();

        // Critical: Verify parameterized query is used
        $queryBuilder->expects($this->once())
            ->method('setParameter')
            ->with('email', Module::API_USER_EMAIL)
            ->willReturnSelf();

        $result = $this->createMock(\Doctrine\DBAL\Result::class);
        $result->method('fetchOne')->willReturn(false);

        $queryBuilder->method('execute')->willReturn($result);

        $this->queryBuilderFactory
            ->method('create')
            ->willReturn($queryBuilder);

        $service = new ApiUserService($this->queryBuilderFactory);
        $user = $this->createMock(\OxidEsales\Eshop\Application\Model\User::class);

        $service->loadApiUser($user);
    }

    public function testResetPasswordUsesParameterizedQuery(): void
    {
        $queryBuilder = $this->createMock(\Doctrine\DBAL\Query\QueryBuilder::class);

        $queryBuilder->expects($this->once())
            ->method('update')
            ->with('oxuser')
            ->willReturnSelf();

        $queryBuilder->expects($this->exactly(2))
            ->method('set')
            ->willReturnSelf();

        $queryBuilder->expects($this->once())
            ->method('where')
            ->with('OXID = :userId')
            ->willReturnSelf();

        // Critical: All values must be parameterized
        $queryBuilder->expects($this->exactly(3))
            ->method('setParameter')
            ->willReturnSelf();

        $queryBuilder->method('execute');

        $this->queryBuilderFactory
            ->method('create')
            ->willReturn($queryBuilder);

        $service = new ApiUserService($this->queryBuilderFactory);
        $service->resetPassword('test-user-id');
    }

    #[DataProvider('sqlInjectionUserIdProvider')]
    public function testResetPasswordWithMaliciousUserId(string $maliciousUserId): void
    {
        $queryBuilder = $this->createMock(\Doctrine\DBAL\Query\QueryBuilder::class);
        $queryBuilder->method('update')->willReturnSelf();
        $queryBuilder->method('set')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();

        // Verify the malicious userId is passed as parameter (safe)
        $capturedUserId = null;
        $queryBuilder->method('setParameter')
            ->willReturnCallback(function ($name, $value) use (&$capturedUserId, $queryBuilder) {
                if ($name === 'userId') {
                    $capturedUserId = $value;
                }
                return $queryBuilder;
            });

        $queryBuilder->method('execute');

        $this->queryBuilderFactory
            ->method('create')
            ->willReturn($queryBuilder);

        $service = new ApiUserService($this->queryBuilderFactory);
        $service->resetPassword($maliciousUserId);

        // Verify the malicious string was passed as parameter (will be escaped)
        $this->assertEquals($maliciousUserId, $capturedUserId);
    }

    public static function sqlInjectionUserIdProvider(): array
    {
        return [
            'basic_injection' => ["' OR '1'='1"],
            'union_select' => ["' UNION SELECT * FROM oxuser WHERE '1'='1"],
            'drop_table' => ["'; DROP TABLE oxuser; --"],
            'comment_bypass' => ["admin'--"],
            'hex_encoded' => ["\x27\x20\x4f\x52\x20\x27\x31\x27\x3d\x27\x31"],
            'double_encoding' => ["%27%20OR%20%271%27%3D%271"],
        ];
    }

    // ===========================================
    // PASSWORD SECURITY TESTS
    // ===========================================

    public function testResetPasswordGeneratesRandomPlaceholder(): void
    {
        $capturedPlaceholders = [];

        $queryBuilder = $this->createMock(\Doctrine\DBAL\Query\QueryBuilder::class);
        $queryBuilder->method('update')->willReturnSelf();
        $queryBuilder->method('set')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('setParameter')
            ->willReturnCallback(function ($name, $value) use (&$capturedPlaceholders, $queryBuilder) {
                if ($name === 'placeholder') {
                    $capturedPlaceholders[] = $value;
                }
                return $queryBuilder;
            });
        $queryBuilder->method('execute');

        $this->queryBuilderFactory
            ->method('create')
            ->willReturn($queryBuilder);

        $service = new ApiUserService($this->queryBuilderFactory);

        // Call reset multiple times
        $service->resetPassword('user1');
        $service->resetPassword('user2');

        // Verify placeholders are different (random)
        $this->assertCount(2, $capturedPlaceholders);
        $this->assertNotEquals($capturedPlaceholders[0], $capturedPlaceholders[1]);
    }

    public function testResetPasswordPlaceholderHasSufficientLength(): void
    {
        $capturedPlaceholder = null;

        $queryBuilder = $this->createMock(\Doctrine\DBAL\Query\QueryBuilder::class);
        $queryBuilder->method('update')->willReturnSelf();
        $queryBuilder->method('set')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('setParameter')
            ->willReturnCallback(function ($name, $value) use (&$capturedPlaceholder, $queryBuilder) {
                if ($name === 'placeholder') {
                    $capturedPlaceholder = $value;
                }
                return $queryBuilder;
            });
        $queryBuilder->method('execute');

        $this->queryBuilderFactory
            ->method('create')
            ->willReturn($queryBuilder);

        $service = new ApiUserService($this->queryBuilderFactory);
        $service->resetPassword('test-user');

        // Verify placeholder is at least 64 characters (32 bytes * 2 hex chars)
        $this->assertGreaterThanOrEqual(64, strlen($capturedPlaceholder));
    }

    public function testResetPasswordClearsSalt(): void
    {
        $capturedSalt = null;

        $queryBuilder = $this->createMock(\Doctrine\DBAL\Query\QueryBuilder::class);
        $queryBuilder->method('update')->willReturnSelf();
        $queryBuilder->method('set')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('setParameter')
            ->willReturnCallback(function ($name, $value) use (&$capturedSalt, $queryBuilder) {
                if ($name === 'salt') {
                    $capturedSalt = $value;
                }
                return $queryBuilder;
            });
        $queryBuilder->method('execute');

        $this->queryBuilderFactory
            ->method('create')
            ->willReturn($queryBuilder);

        $service = new ApiUserService($this->queryBuilderFactory);
        $service->resetPassword('test-user');

        // Verify salt is cleared (empty string)
        $this->assertSame('', $capturedSalt);
    }

    // ===========================================
    // API USER EMAIL CONSTANT TESTS
    // ===========================================

    public function testApiUserEmailIsHardcoded(): void
    {
        // Verify the API user email is a constant, not user-provided
        $reflection = new ReflectionClass(Module::class);
        $constants = $reflection->getConstants();

        $this->assertArrayHasKey('API_USER_EMAIL', $constants);
        $this->assertIsString($constants['API_USER_EMAIL']);
        $this->assertNotEmpty($constants['API_USER_EMAIL']);
    }

    public function testApiUserEmailIsValidFormat(): void
    {
        // Verify email format to prevent injection
        $email = Module::API_USER_EMAIL;

        $this->assertMatchesRegularExpression(
            '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            $email,
            'API_USER_EMAIL must be a valid email format'
        );
    }
}
