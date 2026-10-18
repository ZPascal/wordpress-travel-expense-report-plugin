# Travel Expense Report Plugin — Installation & Testing Guide

[![CI](https://github.com/YOUR-ORG/dpsg-wordpress-homepage-plugin/actions/workflows/ci.yml/badge.svg)](https://github.com/YOUR-ORG/dpsg-wordpress-homepage-plugin/actions/workflows/ci.yml)
[![WP Plugin Check](https://github.com/YOUR-ORG/dpsg-wordpress-homepage-plugin/actions/workflows/wp-check.yml/badge.svg)](https://github.com/YOUR-ORG/dpsg-wordpress-homepage-plugin/actions/workflows/wp-check.yml)
[![License: GPL v2+](https://img.shields.io/badge/License-GPLv2%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![PHP 8.1+](https://img.shields.io/badge/PHP-8.1%2B-777BB4.svg?logo=php)](https://www.php.net/)

## Overview

This is a WordPress plugin for handling travel expense report submissions. It includes:
- Dynamic form with receipt uploads
- Ticket upload validation (only required if ticket data is entered)
- PDF generation with receipts
- Email delivery to admin and optionally to the user
- Configurable settings within WordPress admin
- Comprehensive testing & CI/CD pipeline (GitHub Actions)
- Minified CSS/JS assets for production

## System Requirements

- **WordPress**: 5.0+
- **PHP**: 8.1+
- **TCPDF**: For PDF generation (included in vendor/)
- **FPDI**: PDF template support (included in vendor/)

## Installation

### 1. Install as WordPress Plugin

1. Download or clone this repository into your WordPress `wp-content/plugins/` directory:
   ```bash
   cd /path/to/wordpress/wp-content/plugins
   git clone <repository-url> dpsg-travel-expense-report
   ```

2. Install PHP dependencies:
   ```bash
   cd dpsg-travel-expense-report
   composer install
   ```

3. Activate the plugin in WordPress admin:
   - Go to **Plugins** → Find "Travel Expense Report" → Click **Activate**

### 2. Configuration

After activation, go to **Settings** → **Reisekostenabrechnung** (Travel Expense Report):

#### General Settings
- **Internal Recipient Email**: Email address that receives submitted reports
- **Send a copy to the customer**: Enable/disable sending a copy to the submitter
- **Delete uploads after send**: Remove uploaded files after email is sent
- **Temp folder name**: Name of the temporary folder under `get_temp_dir()` (default: `travel-expense-report`)
- **Subject prefix**: Optional prefix for email subjects

#### Email Templates
- **Internal Subject & Message**: For the admin notification email
- **Customer Subject & Message**: For the copy sent to the user
- **Success/Failure Messages**: Displayed to users on the frontend

#### Logging
- **Log Prefix**: Prefix for error_log lines for debugging

### 3. Usage

Add the shortcode to any page or post:

```
[travel_expense_report]
```

Users can then:
1. Fill in personal details (name, IBAN, BIC, event)
2. Add purchases/receipts (with file uploads)
3. Add trips/mileage (with kilometer calculations)
4. Add train tickets (with file uploads)
5. Submit the form
6. Receive confirmation (and optional copy email)

## Testing

### Setup

Install dev dependencies:

```bash
composer require --dev phpunit/phpunit:^9.5
```

Or use the included composer.json:

```bash
composer install
```

### Run Tests

```bash
# Run all tests
composer test

# Run with verbose output
composer test:verbose

# Run specific test file
./vendor/bin/phpunit tests/HelpersTest.php

# Run specific test
./vendor/bin/phpunit tests/HelpersTest.php::HelpersTest::testTerReceiptBasenameFromValue_url_query_file
```

### Test Coverage

To generate coverage report:

```bash
composer test:coverage
```

This creates an HTML report in the `coverage/` directory.

## File Structure

```
.
├── travel_expense_report.php       # Main plugin file
├── assets/
│   ├── css/travel-expense-report.css
│   └── js/travel-expense-report.js
├── templates/
│   ├── travel-expense-report-form.php      # Frontend form
│   └── travel-expense-report-email.php     # Email template
├── vendor/                                   # Dependencies (TCPDF, FPDI)
├── tests/
│   ├── bootstrap.php                        # Test setup/WP stubs
│   └── HelpersTest.php                      # Unit tests
├── composer.json                            # PHP dependencies
├── phpunit.xml.dist                         # PHPUnit configuration
├── README-TESTS.md                          # Testing documentation
└── README.md                                # This file
```

## Troubleshooting

### Emails not sending
1. Check WordPress mail configuration (SMTP plugin may be needed)
2. Review error logs: **Settings** → **Reisekostenabrechnung** → "Cleanup log"
3. Use the "Send test email" button in settings to test configuration

### File upload issues
1. Check temp folder permissions: Ensure `{get_temp_dir()}/travel-expense-report/` is writable
2. Verify max upload size in PHP settings (`php.ini`)
3. Check available disk space

### PDF generation fails
1. TCPDF requires GD library; verify it's enabled in PHP
2. Check file permissions on temp directory
3. Review WordPress debug log for specific errors

## Features

✅ Dynamic form with add/remove rows
✅ File upload with secure temp storage
✅ Receipt preview (images and PDFs)
✅ Kilometer/mileage calculation
✅ PDF generation with receipt attachments
✅ Optional customer copy email
✅ Configurable email templates with placeholders
✅ Admin cleanup log for file deletion tracking
✅ Responsive mobile design
✅ Security: Path traversal prevention, nonce validation, XSS escaping

## Security Notes

- All file uploads are stored in a plugin-scoped temp folder (not accessible via web)
- Files are served via a secure AJAX endpoint with nonce validation
- Email addresses are validated and sanitized
- Database is not used; state is minimal (settings only via WordPress options)
- All user input is escaped for HTML output

## Support & Development

For issues, feature requests, or contributions:

### Contribution Process
1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-feature`
3. Make your changes
4. Run tests: `composer test`
5. Build minified assets: `npm run build`
6. Create a pull request (see [PR Template](.github/pull_request_template.md))

## License

This plugin is licensed under the GNU General Public License v2.0 or later (GPL-2.0-or-later).

## Changelog

See individual commits in the repository for detailed changes.

