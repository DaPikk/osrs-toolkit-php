# OpenSRS PHP Toolkit — PHP 7.4–8.4 Fork

[![PHP 7.4](https://github.com/DaPikk/osrs-toolkit-php/actions/workflows/php74.yml/badge.svg)](https://github.com/DaPikk/osrs-toolkit-php/actions/workflows/php74.yml)
[![PHP 8.0](https://github.com/DaPikk/osrs-toolkit-php/actions/workflows/php80.yml/badge.svg)](https://github.com/DaPikk/osrs-toolkit-php/actions/workflows/php80.yml)
[![PHP 8.1](https://github.com/DaPikk/osrs-toolkit-php/actions/workflows/php81.yml/badge.svg)](https://github.com/DaPikk/osrs-toolkit-php/actions/workflows/php81.yml)
[![PHP 8.2](https://github.com/DaPikk/osrs-toolkit-php/actions/workflows/php82.yml/badge.svg)](https://github.com/DaPikk/osrs-toolkit-php/actions/workflows/php82.yml)
[![PHP 8.3](https://github.com/DaPikk/osrs-toolkit-php/actions/workflows/php83.yml/badge.svg)](https://github.com/DaPikk/osrs-toolkit-php/actions/workflows/php83.yml)
[![PHP 8.4](https://github.com/DaPikk/osrs-toolkit-php/actions/workflows/php84.yml/badge.svg)](https://github.com/DaPikk/osrs-toolkit-php/actions/workflows/php84.yml)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE)

A community-maintained fork of the OpenSRS PHP Toolkit with compatibility for **PHP 7.4 through PHP 8.4**.
This project is based on the original OpenSRS toolkit and contains additional compatibility fixes, modernization work, and other improvements intended for use with current PHP environments.

> [!IMPORTANT]
> This repository is **not an official OpenSRS release**.
>
> It is an independently maintained fork based on the official OpenSRS PHP Toolkit.

---

## Features

* ✅ PHP **8.4** (from 7.4+) support
* ✅ Tested using GitHub Actions
* ✅ Composer-based installation
* ✅ PSR-4 autoloading
* ✅ OpenSRS Domains API support
* ✅ JSON and YAML support
* ✅ Legacy OpenSRS API compatibility
* ✅ Additional fixes and modernization
* ✅ MIT licensed

---

## Table of Contents

* [Requirements](#requirements)
* [PHP Compatibility](#php-compatibility)
* [Installation](#installation)
* [Configuration](#configuration)
* [Bootstrapping](#bootstrapping)
* [Usage](#usage)
* [Legacy API](#legacy-api)
* [Testing](#testing)
* [OpenSRS Environments](#opensrs-environments)
* [Troubleshooting](#troubleshooting)
* [Upstream Project](#upstream-project)
* [Support](#support)
* [Documentation](#documentation)
* [License](#license)

---

## Requirements

The maintained fork requires a modern PHP environment.

| Requirement              |           Status          |
| ------------------------ | :-----------------------: |
| PHP 8.4                  |    ✅ Required / Tested    |
| Composer                 |         ✅ Required        |
| OpenSSL                  |         ✅ Required        |
| cURL / `ext-curl`        |     ✅ Required for OMA    |
| JSON                     |    ✅ Included with PHP    |
| OpenSRS reseller account | ✅ Required for API access |

Check your PHP version:

```bash
php -v
```

Verify required PHP extensions:

```bash
php -m | grep -E 'curl|openssl|json'
```

---

## PHP Compatibility

This fork targets:

```text
PHP >= 7.4 >= 8.0 < 9.0
```

The corresponding Composer requirement is:

```json
{
    "require": {
        "php": "^7.4 || ^8.0",
    }
}
```

### Compatibility status

| PHP Version       |             Status                      |
| ----------------- | :-------------------------------------: |
| PHP 8.4           |            ✅ Tested                    |
| PHP 7.4 and newer |  ⚠️ Tested and supported from PHP 7.4+  |
| PHP 9.x           | ⚠️ Not yet declared compatible          |

PHP compatibility is validated through the project's GitHub Actions test workflow.

> The original OpenSRS toolkit predates PHP 7.x.
> PHP 8.4 compatibility applies specifically to this maintained fork.

---

## Installation

Install this fork using Composer:

```bash
composer require DaPikk/osrs-toolkit-php
```

Composer will install the package and generate the required autoloader:

```text
vendor/autoload.php
```

### Replacing the upstream package

This fork declares itself as a replacement for:

```text
opensrs/osrs-toolkit-php
```

This allows applications depending on the original package to use the maintained fork where appropriate.

---

## Configuration

Create an OpenSRS configuration file using the supplied configuration template.

For example:

```text
config/openSRS_config.php
```

Configure the file with your OpenSRS reseller credentials and API settings.

### Security

> [!WARNING]
> Never commit production API keys, passwords, or reseller credentials to your Git repository.

For production environments, sensitive configuration should preferably be loaded from:

* environment variables;
* secrets management;
* deployment configuration;
* files stored outside the public web root.

---

## Bootstrapping

Load the Composer autoloader followed by your OpenSRS configuration:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/openSRS_config.php';
```

Composer autoloading is the recommended approach when using this fork.

---

## Usage

A basic OpenSRS request can be performed using the toolkit's `Request` class.

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/openSRS_config.php';

try {
    $request = new Request();

    $response = $request->process('array', $data);

    var_dump($response->resultRaw);
} catch (\OpenSRS\Exception $e) {
    echo 'OpenSRS API error: ' . $e->getMessage();
}
```

The contents of `$data` depend on the OpenSRS command being executed.

Typical API operations include:

* domain availability checks;
* domain registrations;
* renewals;
* transfers;
* nameserver changes;
* contact updates;
* domain locking;
* authorization code management.

Refer to the OpenSRS API documentation for the attributes required by each command.

---

## Example: Domain Lookup

A simple domain lookup request may look like:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/openSRS_config.php';

$data = [
    'func' => 'lookupLookupDomain',
    'data' => [
        'domain' => 'example.com',
    ],
];

try {
    $request = new Request();

    $response = $request->process('array', $data);

    var_dump($response->resultRaw);
} catch (\OpenSRS\Exception $e) {
    echo 'OpenSRS API error: ' . $e->getMessage();
}
```

---

## Legacy API

Backward compatibility with applications using the older OpenSRS toolkit API is retained where possible.

```php
<?php

require_once 'your_root_path/opensrs/openSRS_loader.php';

$data = [
    'func' => 'lookupLookupDomain',
    'data' => [
        'domain' => 'example.com',
    ],
];

$osrsHandler = processOpenSRS('array', $data);

var_dump($osrsHandler);
```

> [!NOTE]
> New integrations should use Composer autoloading and the modern API instead of the legacy loader.

---

## Testing

Install development dependencies:

```bash
composer install
```

Run the test suite:

```bash
vendor/bin/phpunit
```

You can also run Composer's platform checks:

```bash
composer check-platform-reqs
```

### GitHub Actions

PHP 8.4 compatibility is continuously checked using GitHub Actions.

The status badge at the top of this README reflects the current CI result:

```md
[![PHP 8.4 Tests](https://github.com/DaPikk/osrs-toolkit-php/actions/workflows/tests.yml/badge.svg)](https://github.com/DaPikk/osrs-toolkit-php/actions/workflows/tests.yml)
```

This means the PHP compatibility status displayed in the README comes directly from the project's actual test workflow.

---

## OpenSRS Environments

OpenSRS provides separate production and testing environments.

### Production

```text
Server: rr-n1-tor.opensrs.net
Port:   55443
```

Production API access normally requires:

* an OpenSRS reseller account;
* a production API key;
* authorization of the connecting server's IP address.

### Horizon Test Environment

```text
Server: horizon.opensrs.net
Port:   55443
```

The Horizon environment can be used for development and testing without creating real production registrations.

> Production and test environments use separate credentials.

---

## Troubleshooting

### Check PHP

```bash
php -v
```

### Check loaded extensions

```bash
php -m
```

At minimum, verify the availability of:

```text
curl
json
openssl
```

### Check Composer

```bash
composer diagnose
```

and:

```bash
composer check-platform-reqs
```

### Check PHP configuration

```bash
php --ini
```

### Test OpenSRS connectivity

Production:

```bash
openssl s_client -connect rr-n1-tor.opensrs.net:55443
```

Horizon:

```bash
openssl s_client -connect horizon.opensrs.net:55443
```

If authentication fails, verify:

* reseller username;
* API key;
* production vs. test credentials;
* authorized server IP addresses;
* selected OpenSRS environment.

---

## Upstream Project

This project is derived from:

**OpenSRS PHP Toolkit**

```text
https://github.com/OpenSRS/osrs-toolkit-php
```

Composer package:

```text
opensrs/osrs-toolkit-php
```

The original project and its authors remain the source of the underlying OpenSRS PHP Toolkit implementation.

### Changes in this fork

This fork focuses on maintaining usability with current PHP versions and includes changes such as:

* PHP 8.4 compatibility;
* deprecated PHP behavior fixes;
* compatibility updates;
* dependency updates;
* test-suite modernization;
* code cleanup;
* additional bug fixes and maintenance changes.

See the repository's commit history and releases for the complete list of changes.

---

## Fork Maintenance

This repository is maintained independently from OpenSRS.

When reporting a problem, first determine whether it relates to:

### This fork

Examples:

* PHP 8.4 compatibility;
* fork-specific changes;
* regressions introduced by modernization;
* Composer dependency issues;
* failing tests.

Report these through this repository's issue tracker:

```text
https://github.com/DaPikk/osrs-toolkit-php/issues
```

### OpenSRS API

Examples:

* reseller authentication;
* API commands;
* domain registration behavior;
* OpenSRS account configuration;
* registry responses.

These should generally be verified against the official OpenSRS documentation or raised with OpenSRS Support.

---

## Support

### Fork support

For bugs related specifically to this maintained version:

```text
https://github.com/DaPikk/osrs-toolkit-php/issues
```

When reporting a problem, include where relevant:

* PHP version;
* package version;
* operating system;
* failing API operation;
* exception message;
* minimal reproduction example.

Never include:

* API keys;
* passwords;
* reseller credentials;
* authorization codes;
* other secrets.

### OpenSRS support

For service-side or OpenSRS API connectivity problems, consult the official OpenSRS support resources.

---

## Documentation

### OpenSRS

* **Domains API Documentation**
  https://domains.opensrs.guide/

* **API Quick Start**
  https://domains.opensrs.guide/docs/quickstart

* **Troubleshooting**
  https://domains.opensrs.guide/docs/troubleshooting

### Original Toolkit

* **OpenSRS Toolkit Repository**
  https://github.com/OpenSRS/osrs-toolkit-php

* **OpenSRS Toolkit Wiki**
  https://github.com/OpenSRS/osrs-toolkit-php/wiki

### This Fork

* **Source**
  https://github.com/DaPikk/osrs-toolkit-php

* **Issues**
  https://github.com/DaPikk/osrs-toolkit-php/issues

* **Releases**
  https://github.com/DaPikk/osrs-toolkit-php/releases

---

## Project Status

| Component                | Status                    |
| ------------------------ | ------------------------- |
| Project type             | Community-maintained fork |
| Based on                 | OpenSRS PHP Toolkit       |
| PHP requirement          | `^8.4`                    |
| PHP 8.4 CI               | ✅ Enabled                 |
| Dependency management    | Composer                  |
| Autoloading              | PSR-4                     |
| License                  | MIT                       |
| Official OpenSRS release | ❌ No                      |

---

## Credits

This project is based on the original **OpenSRS PHP Toolkit** developed by OpenSRS / Tucows and its contributors.

Original authors include:

* Colin Campbell
* Kris Atkinson

Additional PHP 8.4 compatibility work, maintenance, and improvements are provided by the maintainers of this fork.

---

## License

This project is distributed under the **MIT License**.

See [`LICENSE`](LICENSE) for details.

---

<p align="center">
  <strong>OpenSRS PHP Toolkit — PHP 8.4 Fork</strong>
  <br>
  Community-maintained for modern PHP environments.
</p>
