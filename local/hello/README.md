# Hello World Plugin (local_hello) - v1.0.1

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
- **CI/CD Testing**: Enhanced functionality for automated deployment testing

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
    ├── hello_test.php       # PHPUnit test cases
    └── cicd_test.php        # CI/CD specific test cases
```

## Testing

This plugin includes comprehensive PHPUnit tests that are automatically run by the CI/CD pipeline:

```bash
# Run tests manually
vendor/bin/phpunit local/hello/tests/hello_test.php
vendor/bin/phpunit local/hello/tests/cicd_test.php
```

## Recent Updates (v1.0.1)

- ✅ Added CI/CD test functionality
- ✅ Updated greeting messages with version information
- ✅ Added new language strings for CI/CD testing
- ✅ Enhanced plugin description
- ✅ Added test cases for CI/CD functionality

### CI/CD Testing Features

1. **Version Update**: Plugin version bumped to 1.0.1
2. **New Language Strings**: Added CI/CD test messages
3. **Enhanced UI**: Updated welcome messages with version info
4. **Test Coverage**: Added PHPUnit tests for CI/CD functionality

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