# Logging Framework: Request Logger Remote

**Logging Framework: Request Logger Remote** is an OXID eShop module that provides a **GraphQL API** for remote configuration and activation of the [Request Logger](https://github.com/oxid-support/lf-request-logging) module.

The goal: enable **remote management of request logging** via GraphQL, allowing administrators and automated tools to configure logging settings and activate/deactivate the module without direct admin panel access.

---

## Installation

### Live
```bash
composer require oxid-support/request-logger-remote
```

### Dev
```bash
git clone https://github.com/oxid-support/lf-request-logging-remote.git repo/oxs/request-logger-remote
composer config repositories.oxid-support/request-logger-remote path repo/oxs/request-logger-remote
composer require oxid-support/request-logger-remote:@dev
```

### General

**Important!**
Before activating the module, clear the shop's cache first.
```bash
./vendor/bin/oe-console o:c:c
```

#### Run Migrations
```bash
./vendor/bin/oe-eshop-doctrine_migration migrations:migrate oxsrequestloggerremote
```

#### Activation
```bash
./vendor/bin/oe-console o:m:ac oxsrequestloggerremote
```

---

## Setup Workflow

After activating the module, follow these steps to enable remote access:

1. **Open the module settings** in the admin panel:
   `Extensions → Modules → Request Logger Remote → Settings`

2. **Copy the Setup Token** (click on the field to copy)

3. **Send the token** via email to `support@oxid-esales.com`

4. **Wait for confirmation** - OXID Support will set the API password using the token and notify you when remote access is ready

The Settings page displays a workflow checklist showing your progress through these steps.

---

## Module Information

- **Module ID**: `oxsrequestloggerremote`
- **Module Title**: OXS :: Logging Framework :: Request Logger Remote
- **Version**: 1.0.0
- **Author**: support@oxid-esales.com
- **Supported OXID Versions**: 7.x
- **PHP Version**: 8.2+

### Dependencies

This module requires:
- `oxid-support/request-logger` - The core logging module
- `oxid-esales/graphql-base` - OXID GraphQL framework
- `oxid-esales/graphql-configuration-access` - Provides shared DataTypes

---

## Features

- **Remote Settings Management**
    - Query and modify all Request Logger settings via GraphQL
    - Change log level (standard/detailed)
    - Enable/disable frontend and admin logging
    - Configure redaction settings

- **Module Activation Control**
    - Activate/deactivate the Request Logger module remotely
    - Check current activation status

- **Secure Access**
    - Dedicated API user group (`oxsrequestlogger_api`)
    - One-time setup token for secure password initialization
    - JWT-based authentication via graphql-base
    - Granular permissions: VIEW, CHANGE, ACTIVATE

---

## Authorization

### User Group & Permissions

The module creates a dedicated user group `oxsrequestlogger_api` with the following permissions:

| Permission | Description |
|------------|-------------|
| `REQUEST_LOGGER_VIEW` | Query settings and activation status |
| `REQUEST_LOGGER_CHANGE` | Modify module settings |
| `REQUEST_LOGGER_ACTIVATE` | Activate/deactivate the module |

Shop administrators (`oxidadmin`) also receive these permissions automatically.

### Service User Setup

The migration creates a service user:
- **Email**: `requestlogger-api@oxid-esales.com`
- **Password**: Set via GraphQL mutation using the setup token
- **Group**: `oxsrequestlogger_api`

#### Setting the Password (for OXID Support)

Use the setup token from the shop's admin panel to set the password:

```graphql
mutation {
    requestLoggerSetPassword(token: "<setup-token>", password: "<secure-password>")
}
```

**Security notes:**
- The setup token is generated once during module activation
- The password must be at least 8 characters long
- This mutation only works once - the token is deleted after the password is set
- The Settings page shows "Setup Complete" once the password has been configured

---

## GraphQL API

### Authentication

First, obtain a JWT token:
```graphql
query {
    token(username: "requestlogger-api@oxid-esales.com", password: "your-password")
}
```

Use the token in subsequent requests:
```
Authorization: Bearer <your-token>
```

### Queries

#### Get All Settings
```graphql
query {
    requestLoggerSettings {
        name
        type
        isSupported
    }
}
```

#### Get Individual Settings
```graphql
query {
    requestLoggerLogLevel { name value }
    requestLoggerLogFrontend { name value }
    requestLoggerLogAdmin { name value }
    requestLoggerRedact { name value }
    requestLoggerRedactAllValues { name value }
}
```

#### Check Module Status
```graphql
query {
    requestLoggerIsActive
}
```

### Mutations

#### Change Log Level
```graphql
mutation {
    requestLoggerLogLevelChange(value: "detailed") {
        name
        value
    }
}
```

#### Enable Frontend Logging
```graphql
mutation {
    requestLoggerLogFrontendChange(value: true) {
        name
        value
    }
}
```

#### Enable Admin Logging
```graphql
mutation {
    requestLoggerLogAdminChange(value: true) {
        name
        value
    }
}
```

#### Update Redact List
```graphql
mutation {
    requestLoggerRedactChange(value: "[\"pwd\", \"lgn_pwd\", \"token\", \"apikey\"]") {
        name
        value
    }
}
```

#### Toggle Redact All Values
```graphql
mutation {
    requestLoggerRedactAllValuesChange(value: false) {
        name
        value
    }
}
```

#### Activate Module
```graphql
mutation {
    requestLoggerActivate
}
```

#### Deactivate Module
```graphql
mutation {
    requestLoggerDeactivate
}
```

---

## API Reference

### Queries

| Query | Returns | Permission | Description |
|-------|---------|------------|-------------|
| `requestLoggerSettings` | `[SettingType]` | VIEW | List all settings with types |
| `requestLoggerLogLevel` | `StringSetting` | VIEW | Get log level setting |
| `requestLoggerLogFrontend` | `BooleanSetting` | VIEW | Get frontend logging flag |
| `requestLoggerLogAdmin` | `BooleanSetting` | VIEW | Get admin logging flag |
| `requestLoggerRedact` | `StringSetting` | VIEW | Get redact list (JSON) |
| `requestLoggerRedactAllValues` | `BooleanSetting` | VIEW | Get redact-all flag |
| `requestLoggerIsActive` | `Boolean` | VIEW | Check if module is active |

### Mutations

| Mutation | Parameters | Returns | Permission | Description |
|----------|------------|---------|------------|-------------|
| `requestLoggerSetPassword` | `token: String!, password: String!` | `Boolean` | - | Set initial API user password |
| `requestLoggerLogLevelChange` | `value: String!` | `StringSetting` | CHANGE | Set log level |
| `requestLoggerLogFrontendChange` | `value: Boolean!` | `BooleanSetting` | CHANGE | Toggle frontend logging |
| `requestLoggerLogAdminChange` | `value: Boolean!` | `BooleanSetting` | CHANGE | Toggle admin logging |
| `requestLoggerRedactChange` | `value: String!` | `StringSetting` | CHANGE | Set redact list (JSON) |
| `requestLoggerRedactAllValuesChange` | `value: Boolean!` | `BooleanSetting` | CHANGE | Toggle redact-all |
| `requestLoggerActivate` | - | `Boolean` | ACTIVATE | Activate module |
| `requestLoggerDeactivate` | - | `Boolean` | ACTIVATE | Deactivate module |

---

## Uninstallation

To completely remove the module and its database entries (API user and group), follow these steps in order:

### 1. Deactivate the Module
```bash
./vendor/bin/oe-console o:m:de oxsrequestloggerremote
```

### 2. Revert Migrations
This removes the dedicated API user (`requestlogger-api@oxid-esales.com`) and the user group (`oxsrequestlogger_api`) from the database:
```bash
./vendor/bin/oe-eshop-doctrine_migration migrations:migrate oxsrequestloggerremote first
```

### 3. Remove the Package
```bash
composer remove oxid-support/request-logger-remote
```

### 4. Clear Cache
```bash
./vendor/bin/oe-console o:c:c
```

**Note:** If you skip step 2, the API user and group will remain in the database after uninstallation.

---

## Testing

**Prerequisites:** Install development dependencies at shop level:
```bash
composer install --dev
```

**Run tests from shop root directory:**
```bash
./vendor/bin/phpunit --config=repo/oxs/request-logger-remote/tests/
```
