# FFans 创作者声明

[![License](https://img.shields.io/packagist/l/ffans/creator-declarations.svg?label=许可证)](https://opensource.org/license/mit) [![Latest Stable Version](https://img.shields.io/packagist/v/ffans/creator-declarations.svg)](https://packagist.org/packages/ffans/creator-declarations) [![Release Date](https://img.shields.io/github/release-date/ffans/creator-declarations.svg?label=发布日期&display_date=published_at)](https://github.com/ffans/creator-declarations/releases/latest) [![Total Downloads](https://img.shields.io/packagist/dt/ffans/creator-declarations.svg?label=总下载量)](https://packagist.org/packages/ffans/creator-declarations/stats) [![Monthly Downloads](https://img.shields.io/packagist/dm/ffans/creator-declarations.svg?label=月下载量)](https://packagist.org/packages/ffans/creator-declarations/stats)

为 [Flarum](https://flarum.org/) 帖子提供由创作者自主添加的内容声明。

本扩展帮助作者披露发布内容的来源、真实性、安全风险及商业性质。

> 本扩展借助 AI 辅助制作完成，将根据社区反馈持续优化与维护。

## 预览

![效果图](https://raw.githubusercontent.com/FFans/creator-declarations/main/docs/images/post_zh-Hans.png)
![详情效果图](https://raw.githubusercontent.com/FFans/creator-declarations/main/docs/images/details_zh-Hans.png)

## 功能

- 发布主题或回复主题时可选择多种声明。
- 弹窗展示声明详情及备注。
- 仅有内容来源声明时特殊展示。
- ……

## 可用声明

| 分类     | 声明                         |
| -------- | ---------------------------- |
| 内容来源 | 原创内容                     |
| 内容来源 | 转载内容                     |
| 内容来源 | 参考或引用内容               |
| 真实性   | 内容由 AI 生成               |
| 真实性   | 虚构或情景演绎内容           |
| 真实性   | 个人观点或见解               |
| 安全     | 危险行为，请勿模仿           |
| 安全     | 可能引人不适                 |
| 商业披露 | 包含营销或推广信息           |
| 商业披露 | 包含付费合作、赞助或利益关系 |

## 环境要求

- Flarum `^2.0.0`

Flarum 1.x 计划支持中。

## 安装

使用 Composer 安装：

```sh
composer require ffans/creator-declarations
php flarum cache:clear
```

在 Flarum 管理后台中启用本扩展。

## 更新

```sh
composer update ffans/creator-declarations
php flarum migrate
php flarum cache:clear
```

## 配置

打开 **管理后台 → 扩展 → FFans Creator Declarations**。

| 设置                                 | 默认值   |
| ------------------------------------ | -------- |
| 发布主题时必须选择声明               | 关闭     |
| 回复时必须选择声明                   | 关闭     |
| 每篇帖子最多可选声明数               | 5        |
| 在用户个人主页的“回复”列表中显示声明 | 关闭     |
| 声明排序                             | 内置顺序 |
| 启用声明类型                         | 全部启用 |

本扩展添加以下权限：

- **允许编辑自己的创作者声明** — 控制作者可在多长时间内编辑自己帖子的声明。
- **编辑帖子的创作者声明** — 允许管理员编辑任意帖子声明。

## 使用方法

撰写主题或回复时，选择 **添加自主声明**，勾选适用的声明，填写必要的详细信息后保存。声明将与帖子一同提交。

拥有相应权限的用户可以从帖子操作菜单中编辑已有声明。声明仅供参考，应结合帖子正文内容进行判断。

## 开发

### 本地开发安装

在 Flarum 根目录中配置 Composer 路径仓库并安装扩展：

```powershell
composer config repositories.creator-declarations path "/path/to/your/local/dir/creator-declarations"
composer require ffans/creator-declarations:"@dev"
php flarum extension:enable ffans-creator-declarations
php flarum cache:clear
```

### 前端开发

```powershell
cd js
npm install
npm run check-typings
npm run build
```

使用 `npm run dev` 启动开发构建并热更新。

### 后端测试

安装开发依赖，首次运行时初始化集成测试环境，然后执行测试：

```powershell
composer install
composer test:setup
composer test
```

集成测试使用 MySQL 或 MariaDB。连接信息通过环境变量 `DB_DRIVER=mysql`（默认 `sqlite`）、 `DB_HOST`（默认 `localhost`）、`DB_PORT`（默认 `3306`）、`DB_DATABASE`（默认 `flarum_test`）、`DB_USERNAME`（默认 `root`）、`DB_PASSWORD`（默认 `root`）和 `DB_PREFIX`（默认空）设置；测试目录可通过 `FLARUM_TEST_TMP_DIR_LOCAL` 或 `FLARUM_TEST_TMP_DIR` 指定。

执行 `composer test:setup` 前需要先创建专用测试数据库。初始化会清空指定数据库，因此绝不能使用现有开发站点或生产站点的数据库。后续集成测试中的数据库变更会在事务内执行，可以持续复用已经初始化的测试数据库。

如果只需运行不依赖数据库的单元测试：

```powershell
composer test:unit
```

## 翻译

帮助翻译本扩展，请前往 [Robert Korulczyk's Weblate 平台](https://weblate.rob006.net/projects/flarum2/ffans-creator-declaration/)。

## 链接

- [GitHub](https://github.com/ffans/creator-declarations)
- [Packagist](https://packagist.org/packages/ffans/creator-declarations)
- [英文社区](https://discuss.flarum.org/d/39709)
- [中文社区](https://discuss.flarum.org.cn/d/16544)

## 许可证

本项目基于 [MIT 许可证](https://raw.githubusercontent.com/FFans/creator-declarations/main/LICENSE)发布。
