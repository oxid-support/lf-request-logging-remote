<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSupport\RequestLoggerRemote\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidSupport\RequestLoggerRemote\Core\Module;

/**
 * Admin controller for password reset functionality.
 * This controller only handles the POST action and redirects back to module_config.
 */
final class PasswordResetController extends AdminController
{
    private const USER_ID = 'oxsrequestlogger_api_user';

    private function getQueryBuilderFactory(): QueryBuilderFactoryInterface
    {
        return ContainerFactory::getInstance()->getContainer()->get(QueryBuilderFactoryInterface::class);
    }

    private function getModuleSettingService(): ModuleSettingServiceInterface
    {
        return ContainerFactory::getInstance()->getContainer()->get(ModuleSettingServiceInterface::class);
    }

    /**
     * Resets the API user password to a placeholder and generates a new setup token.
     */
    public function resetPassword(): void
    {
        $userId = md5(self::USER_ID);

        /** @var User $user */
        $user = oxNew(User::class);

        if (!$user->load($userId)) {
            $this->redirectWithError('USER_NOT_FOUND');
            return;
        }

        // Reset password to a placeholder (random hex string)
        $placeholder = bin2hex(random_bytes(32));
        $this->setPasswordPlaceholder($userId, $placeholder);

        // Generate new setup token
        $token = Registry::getUtilsObject()->generateUId();
        $this->getModuleSettingService()->saveString(Module::SETTING_SETUP_TOKEN, $token, Module::MODULE_ID);

        // Pass token via URL parameter to display once
        $this->redirectWithSuccess($token);
    }

    private function setPasswordPlaceholder(string $userId, string $placeholder): void
    {
        $queryBuilder = $this->getQueryBuilderFactory()->create();
        $queryBuilder
            ->update('oxuser')
            ->set('OXPASSWORD', ':placeholder')
            ->set('OXPASSSALT', ':salt')
            ->where('OXID = :userId')
            ->setParameter('placeholder', $placeholder)
            ->setParameter('salt', '')
            ->setParameter('userId', $userId);

        $queryBuilder->execute();
    }

    private function redirectWithSuccess(string $newToken): void
    {
        $url = $this->buildRedirectUrl(['resetSuccess' => '1', 'newToken' => $newToken]);
        Registry::getUtils()->redirect($url, false, 302);
    }

    private function redirectWithError(string $error): void
    {
        $url = $this->buildRedirectUrl(['resetError' => $error]);
        Registry::getUtils()->redirect($url, false, 302);
    }

    private function buildRedirectUrl(array $params = []): string
    {
        $baseUrl = Registry::getConfig()->getCurrentShopUrl() . 'admin/index.php';
        $params = array_merge([
            'cl' => 'module_config',
            'oxid' => Module::MODULE_ID,
            'force_sid' => Registry::getSession()->getId(),
        ], $params);

        return $baseUrl . '?' . http_build_query($params);
    }
}
