# Hello World Plugin (local_hello)

A simple demonstration plugin for Moodle that showcases proper plugin development standards and CI/CD testing.

## Description

This local plugin provides a basic "Hello World" functionality that demonstrates:
- Proper Moodle plugin structure
- Language string management
- Configuration settings
- PHPUnit testing
- Moodle coding standards compliance

## Features

- **Greeting Page**: Displays a personalized greeting to logged-in users
- **Admin Settings**: Configurable display name through Moodle admin interface
- **Language Support**: Proper language string implementation
- **Test Coverage**: Comprehensive PHPUnit tests for CI/CD

## Installation

1. Copy the plugin files to `{moodle}/local/hello/`
2. Visit Site Administration > Notifications to install the plugin
3. Configure the plugin via Site Administration > Plugins > Local plugins > Hello World Plugin

## Usage

1. Navigate to `/local/hello/index.php` on your Moodle site
2. View the personalized greeting message
3. Configure the display name in admin settings

## File Structure

```
local/hello/
├── README.md                 # This documentation
├── version.php              # Plugin metadata and version info
├── lib.php                  # Core plugin functions
├── index.php                # Main plugin page
├── settings.php             # Admin configuration
├── lang/
│   └── en/
│       └── local_hello.php  # English language strings
└── tests/
    └── hello_test.php       # PHPUnit test cases
```

## Testing

This plugin includes comprehensive PHPUnit tests that are automatically run by the CI/CD pipeline:

```bash
# Run tests manually
vendor/bin/phpunit local/hello/tests/hello_test.php
```

## Development

This plugin serves as a template for:
- Proper Moodle plugin development
- CI/CD pipeline testing
- Code quality standards
- Documentation practices

## License

This plugin is licensed under the GNU GPL v3 or later.

## Author

Created as a demonstration plugin for automated CI/CD testing with Moodle plugins. 