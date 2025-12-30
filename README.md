# Request Logger Remote

OXID eShop Modul zur **Fernsteuerung des Request Loggers via GraphQL**.

Ermöglicht OXID Support die Remote-Konfiguration des Request Logger Moduls ohne direkten Admin-Zugang.

---

## System Requirements

- **OXID eShop**: 7.x
- **PHP**: 8.2+
- **Abhängigkeiten**:
  - `oxid-support/request-logger` - Das Basis-Logging-Modul
  - `oxid-esales/graphql-base` - OXID GraphQL Framework
  - `oxid-esales/graphql-configuration-access` - Shared DataTypes

---

## Installation

### Live

```bash
composer require oxid-support/request-logger-remote
```

### Setup

```bash
# 1. Cache leeren
./vendor/bin/oe-console oe:cache:clear

# 2. Migrations ausführen
./vendor/bin/oe-eshop-doctrine_migration migrations:migrate oxsrequestloggerremote

# 3. Modul aktivieren
./vendor/bin/oe-console oe:module:activate oxsrequestloggerremote
```

### Einrichtung des Remote-Zugangs

1. Admin öffnen: `Erweiterungen → Module → Request Logger Remote → Einstell.`
2. Setup-Token aus dem Workflow kopieren (Klick zum Kopieren)
3. Token an `support@oxid-esales.com` senden
4. Warten bis OXID Support den Zugang bestätigt

---

## Deinstallation

```bash
# 1. Modul deaktivieren
./vendor/bin/oe-console oe:module:deactivate oxsrequestloggerremote

# 2. Migration rückgängig machen (entfernt API User und Gruppe)
./vendor/bin/oe-eshop-doctrine_migration migrations:execute oxsrequestloggerremote \
    'OxidSupport\RequestLoggerRemote\Migrations\Version20251223000001' --down

# 3. Paket entfernen
composer remove oxid-support/request-logger-remote

# 4. Cache leeren
./vendor/bin/oe-console oe:cache:clear
```

### Troubleshooting nach Deinstallation

Falls der Shop nach der Deinstallation Fehler wie `ActiveModulesDataProviderBridge::__construct()` wirft:

**Lösung:** `var/configuration/shops/1/active_module_services.yaml` bearbeiten und diese Zeile entfernen:

```yaml
  -
    resource: ../../../../repo/oxs/request-logger-remote/services.yaml
```

Dann Cache leeren: `./vendor/bin/oe-console oe:cache:clear`