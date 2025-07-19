# Moodle Plugin CI - Local Development Setup

This document explains how to use the Moodle Plugin CI for testing Moodle plugins in your local development environment.

## Installation

The Moodle Plugin CI has been installed globally using Composer:

```bash
composer global require moodlehq/moodle-plugin-ci
```

**Version Installed:** 1.5.8

## Configuration

A configuration file `.moodle-plugin-ci.yml` has been created in the root directory with settings optimized for local development.

## Available Commands

### Basic Commands

- `moodle-plugin-ci list` - List all available commands
- `moodle-plugin-ci --version` - Show version information
- `moodle-plugin-ci help <command>` - Get help for a specific command

### Plugin Validation

- `moodle-plugin-ci validate <plugin-path>` - Validate a plugin structure
  - Example: `moodle-plugin-ci validate blocks/html`

### Code Quality Tools

- `moodle-plugin-ci phplint <plugin-path>` - Run PHP syntax checking
- `moodle-plugin-ci codechecker <plugin-path>` - Run Moodle Code Checker
- `moodle-plugin-ci phpcbf <plugin-path>` - Run Code Beautifier and Fixer
- `moodle-plugin-ci phpmd <plugin-path>` - Run PHP Mess Detector
- `moodle-plugin-ci phpcpd <plugin-path>` - Run PHP Copy/Paste Detector
- `moodle-plugin-ci jshint <plugin-path>` - Run JavaScript Lint
- `moodle-plugin-ci csslint <plugin-path>` - Run CSS Lint

### Testing

- `moodle-plugin-ci phpunit <plugin-path>` - Run PHPUnit tests
- `moodle-plugin-ci behat <plugin-path>` - Run Behat tests
- `moodle-plugin-ci parallel <plugin-path>` - Run all tests and analysis in parallel

### Installation and Setup

- `moodle-plugin-ci install` - Install everything required for CI testing
- `moodle-plugin-ci add-config <line>` - Add a line to Moodle config.php
- `moodle-plugin-ci add-plugin <plugin>` - Queue up an additional plugin

### Utilities

- `moodle-plugin-ci shifter <plugin-path>` - Shift YUI modules
- `moodle-plugin-ci coveralls-upload` - Upload code coverage to Coveralls

## Usage Examples

### 1. Validate a Plugin

```bash
# Validate a block plugin
moodle-plugin-ci validate blocks/html

# Validate a module plugin
moodle-plugin-ci validate mod/assign

# Validate a local plugin
moodle-plugin-ci validate local/myplugin
```

### 2. Run Code Quality Checks

```bash
# Run all code quality tools on a plugin
moodle-plugin-ci parallel blocks/html

# Run specific tools
moodle-plugin-ci phplint blocks/html
moodle-plugin-ci codechecker blocks/html
moodle-plugin-ci phpmd blocks/html
```

### 3. Run Tests

```bash
# Run PHPUnit tests
moodle-plugin-ci phpunit blocks/html

# Run Behat tests
moodle-plugin-ci behat blocks/html

# Run all tests and analysis
moodle-plugin-ci parallel blocks/html
```

### 4. Fix Code Issues

```bash
# Automatically fix coding standard issues
moodle-plugin-ci phpcbf blocks/html
```

## Configuration File

The `.moodle-plugin-ci.yml` file contains:

- **Moodle Version:** 4.4 (MOODLE_404_STABLE)
- **Database:** MySQL with default WAMP settings
- **Web Server:** Apache on port 8000
- **PHP Versions:** 8.1, 8.2, 8.3
- **Code Quality Tools:** PHPCS, PHPMD, PHPCPD, JSHint, CSSLint
- **Testing:** PHPUnit and Behat support

## Database Setup

For testing, you may need to create test databases:

```sql
-- Create test databases
CREATE DATABASE moodle_test;
CREATE DATABASE moodle_behat;
CREATE DATABASE moodle_phpunit;
```

## Environment Variables

The configuration includes environment variables for:
- Moodle database settings
- Web server configuration
- Behat testing setup
- PHPUnit testing setup

## Troubleshooting

### Common Issues

1. **Deprecation Warnings**: The deprecation warnings shown are normal for this version and don't affect functionality.

2. **Database Connection**: Ensure your MySQL server is running and accessible.

3. **Plugin Path**: Always specify the full plugin path (e.g., `blocks/html`, not just `html`).

4. **PHP Version**: Ensure you're using a compatible PHP version (8.1+ recommended).

### Getting Help

- Run `moodle-plugin-ci help <command>` for specific command help
- Check the [official documentation](https://moodlehq.github.io/moodle-plugin-ci/)
- Review the configuration file `.moodle-plugin-ci.yml` for settings

## Integration with Development Workflow

### Pre-commit Checks

Add these commands to your development workflow:

```bash
# Before committing changes
moodle-plugin-ci validate <your-plugin>
moodle-plugin-ci phplint <your-plugin>
moodle-plugin-ci codechecker <your-plugin>
moodle-plugin-ci phpunit <your-plugin>
```

### Continuous Integration

The configuration file can be used with CI/CD systems like:
- GitHub Actions
- GitLab CI
- Jenkins
- Travis CI

## Best Practices

1. **Run validation first** - Always validate your plugin structure before running other checks
2. **Use parallel execution** - Use `parallel` command for comprehensive testing
3. **Fix issues incrementally** - Address code quality issues one tool at a time
4. **Keep configuration updated** - Update the configuration file as your project evolves
5. **Test regularly** - Run tests frequently during development

## Plugin Types Supported

The configuration supports all Moodle plugin types:
- blocks/*
- mod/*
- local/*
- auth/*
- enrol/*
- filter/*
- format/*
- report/*
- repository/*
- theme/*
- tool/*
- And many more...

## Next Steps

1. Test the installation with a simple plugin validation
2. Configure your database settings if needed
3. Set up your development workflow
4. Integrate with your IDE or editor
5. Set up automated testing in your CI/CD pipeline

For more information, visit the [official Moodle Plugin CI documentation](https://moodlehq.github.io/moodle-plugin-ci/). 