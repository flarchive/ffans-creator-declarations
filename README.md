# FFans Creator Declarations

[![License](https://img.shields.io/packagist/l/ffans/creator-declarations.svg)](https://opensource.org/license/mit) [![Latest Stable Version](https://img.shields.io/packagist/v/ffans/creator-declarations.svg)](https://packagist.org/packages/ffans/creator-declarations) [![Release Date](https://img.shields.io/github/release-date/ffans/creator-declarations.svg?display_date=published_at)](https://github.com/ffans/creator-declarations/releases/latest) [![Total Downloads](https://img.shields.io/packagist/dt/ffans/creator-declarations.svg)](https://packagist.org/packages/ffans/creator-declarations/stats) [![Monthly Downloads](https://img.shields.io/packagist/dm/ffans/creator-declarations.svg)](https://packagist.org/packages/ffans/creator-declarations/stats)

A [Flarum](http://flarum.org) extension. Creator-provided content disclosures for the posts.

The extension helps authors describe the source, authenticity, safety context, and commercial nature of each discussion or reply. Declarations are supplied by the author and are not verification by the forum.

## Preview

![Screenshot](https://raw.githubusercontent.com/FFans/creator-declarations/main/docs/images/post.png)
![Screenshot details](https://raw.githubusercontent.com/FFans/creator-declarations/main/docs/images/details.png)

## Features

- Select multiple declarations when starting a discussion or writing a reply.
- Show declaration details and notes in modal.
- Optionally show Original and Repost as post-header tags.
- ……

## Available declarations

| Category              | Declaration                                         |
| --------------------- | --------------------------------------------------- |
| Content source        | Original content                                    |
| Content source        | Reposted or quoted content                          |
| Authenticity          | AI-generated content                                |
| Authenticity          | Fictional or staged content                         |
| Authenticity          | Personal opinions or perspectives                   |
| Safety                | Dangerous behavior; do not imitate                  |
| Safety                | Potentially disturbing                              |
| Commercial disclosure | Contains marketing or promotional information       |
| Commercial disclosure | Paid partnership, sponsorship, or material interest |

## Requirements

- Flarum `^2.0.0`

The current version was developed using Flarum `2.0.0-rc.5`. Flarum 1.x support is planned.

## Installation

Install with Composer:

```sh
composer require ffans/creator-declarations
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
| Show Original and Repost tags in the post header | Off            |
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
```

Use `npm run dev` for a development build that watches for frontend changes.

## Translations

Want to help translate this extension? Visit [Robert Korulczyk's Weblate platform](https://weblate.rob006.net/projects/flarum2/ffans-creator-declaration/).

## Links

- [GitHub](https://github.com/ffans/creator-declarations)
- [Packagist](https://packagist.org/packages/ffans/creator-declarations)
- [Discuss](https://discuss.flarum.org/d/39709)
- [Discuss in Chinese](https://discuss.flarum.org.cn/d/16544)

## License

Released under the [MIT License](https://opensource.org/license/mit).
