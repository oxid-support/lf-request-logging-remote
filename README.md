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
  - `oxid-esales/graphql-configuration-access` - **Muss aktiviert sein** (siehe unten)

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

# 2. GraphQL Base Modul aktivieren (falls noch nicht aktiv)
./vendor/bin/oe-console oe:module:activate oe_graphql_base

# 3. Configuration Access Modul aktivieren (falls noch nicht aktiv)
./vendor/bin/oe-console oe:module:activate oe_graphql_configuration_access

# 4. Migrations ausführen
./vendor/bin/oe-eshop-doctrine_migration migrations:migrate oxsrequestloggerremote

# 5. Modul aktivieren
./vendor/bin/oe-console oe:module:activate oxsrequestloggerremote
```

### Sicherheitshinweis zu graphql-configuration-access

Das `graphql-configuration-access` Modul muss aktiviert sein, da dieses Modul dessen Services intern nutzt. **Dies stellt kein Sicherheitsrisiko dar:**

| Aspekt | Bewertung |
|--------|-----------|
| Wer hat Zugriff auf configuration-access? | Nur `oxidadmin` Gruppe |
| Werden neue User/Gruppen erstellt? | Nein |
| Öffentliche Endpoints? | Nein, alle erfordern Admin-Login |

**Begründung:** Wer in der `oxidadmin` Gruppe ist, hat bereits vollen Admin-Zugang zum Shop. Das Modul fügt lediglich eine GraphQL-Schnittstelle für Funktionen hinzu, die Admins sowieso schon über das Admin-Panel nutzen können. Es werden keine neuen Türen geöffnet.

### Einrichtung des Remote-Zugangs

**Voraussetzungen prüfen** (vor dem Senden des Tokens!):

```bash
# 1. GraphQL Base Modul muss installiert und aktiviert sein
./vendor/bin/oe-console oe:module:activate oe_graphql_base

# 2. Configuration Access Modul muss aktiviert sein
./vendor/bin/oe-console oe:module:activate oe_graphql_configuration_access
```

**Dann Token an Support senden:**

1. Admin öffnen: `Erweiterungen → Module → Request Logger Remote → Einstell.`
2. Setup-Token aus dem Workflow kopieren (Klick zum Kopieren)
3. Token an `support@oxid-esales.com` senden
4. Warten bis OXID Support den Zugang bestätigt

> **Wichtig:** Ohne aktiviertes GraphQL Base und Configuration Access Modul kann der Support den Token nicht verwenden!

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