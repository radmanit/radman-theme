# Radman Theme

A lightweight, fast, and SEO-friendly WordPress theme built for content-driven websites, blogs, news portals, and custom WordPress projects.

![WordPress](https://img.shields.io/badge/WordPress-Theme-blue)
![PHP](https://img.shields.io/badge/PHP-8%2B-purple)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-06B6D4)
![License](https://img.shields.io/badge/License-MIT-green)

---

## Overview

Radman Theme is a custom WordPress theme focused on performance, maintainability, and search engine optimization.

The theme includes built-in SEO enhancements, breadcrumb schema generation, Jalali date support, external post images, backlink management, and GitHub-based update support while keeping the codebase clean and lightweight.

---

## Features

### SEO Optimizations

- Automatic meta description generation
- Clean `<head>` output
- Removes unnecessary WordPress meta tags
- Structured breadcrumb schema (JSON-LD)
- SEO-friendly archive and category pages

### Performance

- Lightweight architecture
- Optimized asset loading
- Versioned CSS files using file modification timestamps
- Minimal dependencies

### Content Management

- Featured image support
- External image fallback support
- Dynamic breadcrumbs
- Custom backlink management system
- Shortcode support

### Persian Support

- Built-in Gregorian to Jalali date conversion
- Persian month names
- RTL-ready structure

### Developer Friendly

- Modular template structure
- TailwindCSS workflow
- Composer support
- GitHub update checker integration

---

## Directory Structure

```text
radman-theme/
│
├── assets/
│   ├── css/
│   ├── fonts/
│   ├── image/
│   └── js/
│
├── partials/
│
├── template-parts/
│   ├── footer/
│   ├── header/
│   └── sections/
│
├── src/
│
├── vendor/
│
├── functions.php
├── header.php
├── footer.php
├── index.php
├── page.php
├── single.php
├── category.php
├── search.php
├── 404.php
├── style.css
└── screenshot.png
```

---

## Installation

### Manual Installation

1. Download the repository.
2. Upload the theme folder to:

```text
wp-content/themes/
```

3. Activate the theme from:

```text
WordPress Dashboard → Appearance → Themes
```

---

## Development Setup

Install PHP dependencies:

```bash
composer install
```

Install Node packages:

```bash
npm install
```

Build Tailwind assets:

```bash
npm run build
```

Watch for changes during development:

```bash
npm run watch
```

---

## External Image Support

Posts can use an external image URL if no featured image is assigned.

Custom field:

```text
external_image_url
```

The theme automatically checks:

1. Featured Image
2. External Image URL
3. Placeholder Image

---

## Breadcrumb System

The theme includes a fully dynamic breadcrumb generator supporting:

- Home page
- Categories
- Single posts
- Pages
- Search results
- Archives
- Authors
- Tags
- 404 pages

Structured data is automatically generated using:

```json
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList"
}
```

---

## Jalali Date Support

Built-in functions allow converting Gregorian dates to Jalali dates without requiring third-party plugins.

Example:

```php
echo get_jalali_date_from_timestamp(time());
```

---

## Backlink Manager

The theme includes a custom post type called:

```text
Backlinks
```

Each backlink contains:

- URL
- Anchor Text

Display backlinks anywhere using:

```php
[backlinks]
```

---

## Theme Update System

The theme supports automatic updates directly from GitHub using:

```php
Plugin Update Checker
```

Repository:

```text
https://github.com/radmanit/radman-theme
```

---

## Screenshots

### Homepage

![Homepage](screenshot.png)

---

## Requirements

- PHP 8.0+
- WordPress 6.0+
- Composer
- Node.js 18+

---

## Author

**Radman**

GitHub:

https://github.com/radmanit

---

## License

This project is licensed under the MIT License.

See the LICENSE file for more information.
