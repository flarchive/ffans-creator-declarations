# FFans Creator Declarations

[![License](https://img.shields.io/packagist/l/ffans/creator-declarations.svg)](https://opensource.org/license/mit) [![Flarum](https://img.shields.io/badge/dynamic/json?url=https%3A%2F%2Fraw.githubusercontent.com%2FFFans%2Fcreator-declarations%2F1.x%2Fcomposer.json&query=%24.require%5B%22flarum%2Fcore%22%5D&label=Flarum)](https://docs.flarum.org/1.x/) [![Latest Version](https://img.shields.io/github/v/tag/FFans/creator-declarations?filter=v0.1.*&sort=semver&label=version)](https://github.com/FFans/creator-declarations/releases) [![Release Date](https://img.shields.io/github/release-date/ffans/creator-declarations.svg?display_date=published_at)](https://github.com/ffans/creator-declarations/releases/latest) [![Total Downloads](https://img.shields.io/packagist/dt/ffans/creator-declarations.svg)](https://packagist.org/packages/ffans/creator-declarations/stats) [![Monthly Downloads](https://img.shields.io/packagist/dm/ffans/creator-declarations.svg)](https://packagist.org/packages/ffans/creator-declarations/stats)

A [Flarum](http://flarum.org) extension. Creator-provided content disclosures for the posts.

The extension helps authors describe the source, authenticity, safety context, and commercial nature of each posts.

> This extension was built with AI assistance and will continue to be improved and maintained based on community feedback.

## Preview

![Screenshot](https://raw.githubusercontent.com/FFans/creator-declarations/1.x/docs/images/post.png)
![Screenshot details](https://raw.githubusercontent.com/FFans/creator-declarations/1.x/docs/images/details.png)

## Features

- Select multiple declarations when starting a discussion or writing a reply.
- Show declaration details and notes in modal.
- Display source declarations as standalone notices when no other declaration types are selected.
- ……

## Available declarations

| Category              | Declaration                                         |
| --------------------- | --------------------------------------------------- |
| Content source        | Original content                                    |
| Content source        | Reposted content                                    |
| Content source        | Referenced or quoted content                        |
| Authenticity          | AI-generated content                                |
| Authenticity          | Fictional or staged content                         |
| Authenticity          | Personal opinions or perspectives                   |
| Safety                | Dangerous behavior; do not imitate                  |
| Safety                | Potentially disturbing                              |
| Commercial disclosure | Contains marketing or promotional information       |
| Commercial disclosure | Paid partnership, sponsorship, or material interest |

## Requirements

- Flarum `^1.8.0`

## Installation

Install with Composer:

```sh
composer require ffans/creator-declarations:^0.1.0
php flarum cache:clear
```

You can enable the extension from the Flarum administration dashboard.

## Updating

```sh
composer update ffans/creator-declarations
php flarum migrate
php flarum cache:clear
```

## Configuration

Open **Administration → Extensions → FFans Creator Declarations**.

| Setting                                          | Default        |
| ------------------------------------------------ | -------------- |
| Require a declaration when starting a discussion | Off            |
| Require a declaration when replying              | Off            |
| Maximum declarations per post                    | 5              |
| Show declarations in user profile Posts lists    | Off            |
| Declaration order                                | Built-in order |
| Individual declaration types                     | All enabled    |

The extension also adds these permissions:

- **Allow editing own creator declarations** — controls how long authors may edit declarations on their own posts.
- **Edit creator declarations on posts** — allows moderators to edit declarations on any post.

## Usage

When composing a discussion or reply, select **Add self declarations**, choose the declarations that apply, add any required details, and save. The declarations are submitted together with the post.

Users with permission can edit existing declarations from the post controls menu. Disclosures are informational and should be considered together with the post content.

## Development

### Local development installation

Configure a Composer path repository from the Flarum root directory and install the extension:

```powershell
composer config repositories.creator-declarations path "/path/to/your/local/dir/creator-declarations"
composer require ffans/creator-declarations:"@dev"
php flarum extension:enable ffans-creator-declarations
php flarum cache:clear
```

### Frontend development

```powershell
cd js
npm install
npm run check-typings
npm run build
php flarum cache:clear; & php flarum assets:publish
```

Use `npm run dev` for a development build that watches for frontend changes.

### Backend tests

Install the development dependencies, initialize the integration-test environment once, then run the test suite:

```sh
composer install
composer test:setup
composer test
```

Integration tests use a MySQL or MariaDB instance. Connection details are configured with `DB_HOST` (default `localhost`), `DB_PORT` (default `3306`), `DB_DATABASE` (default `flarum_test`), `DB_USERNAME` (default `root`), `DB_PASSWORD` (default empty), and `DB_PREFIX` (default empty). The test directory can be configured with `FLARUM_TEST_TMP_DIR_LOCAL` or `FLARUM_TEST_TMP_DIR`.

Create the dedicated test database before running `composer test:setup`. Setup wipes the selected database, so never point these variables at a development or production Flarum database. Subsequent integration tests run database changes inside transactions, allowing the initialized test database to be reused.

Run only the database-free unit tests with:

```sh
composer test:unit
```

## Translations

Want to help translate this extension? Visit [Robert Korulczyk's Weblate](https://weblate.rob006.net/projects/flarum/ffans-creator-declarations/).

## Links

- [GitHub](https://github.com/ffans/creator-declarations)
- [Packagist](https://packagist.org/packages/ffans/creator-declarations)
- [Discuss](https://discuss.flarum.org/d/39709)
- [Discuss in Chinese](https://discuss.flarum.org.cn/d/16544)

## License

Released under the [MIT License](https://raw.githubusercontent.com/FFans/creator-declarations/1.x/LICENSE).
