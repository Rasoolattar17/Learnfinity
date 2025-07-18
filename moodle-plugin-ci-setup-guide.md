# Moodle Plugin CI - Local Development Environment Setup Guide

## Overview
This guide demonstrates how to set up and use `moodle-plugin-ci` in your local development environment for validating Moodle plugins and code.

## Prerequisites
- PHP 8.4.5 (installed and configured)
- Composer (installed and configured)
- Git (for version control)
- Node.js and npm (for JavaScript linting)

## Installation Methods

### Method 1: Using Composer (Recommended)

1. **Install moodle-plugin-ci outside the Moodle directory:**
   ```bash
   # From within your Moodle directory
   php composer.phar create-project moodlehq/moodle-plugin-ci ../moodle-plugin-ci ^4
   ```

2. **Verify installation:**
   ```bash
   ../moodle-plugin-ci/bin/moodle-plugin-ci --version
   ```

### Method 2: Using PHAR Package

1. **Download the PHAR file:**
   ```bash
   wget https://github.com/moodlehq/moodle-plugin-ci/releases/download/4.5.8/moodle-plugin-ci.phar
   ```

2. **Test the PHAR version:**
   ```bash
   php moodle-plugin-ci.phar --version
   ```

## Installation Results

✅ **Successfully installed:**
- moodle-plugin-ci version 4.5.8
- PHP CodeSniffer 3.13.2
- PHP Code Beautifier and Fixer (PHPCBF) 3.13.2
- All required dependencies and tools

## Usage Examples

### 1. Running Mustache Template Validation
```bash
# Using Composer installation
../moodle-plugin-ci/bin/moodle-plugin-ci mustache .local/verify_badge

# Using PHAR
php moodle-plugin-ci.phar mustache .local/verify_badge
```

### 2. PHP CodeSniffer (Individual Files)
```bash
# Check a single file with Moodle coding standards
../moodle-plugin-ci/vendor/bin/phpcs --standard=moodle ./brokenfile.php

# Example output:
# FILE: /workspace/brokenfile.php
# FOUND 1 ERROR AFFECTING 1 LINE
# 31 | ERROR | Expected MOODLE_INTERNAL check or config.php inclusion.
```

### 3. PHP Code Beautifier and Fixer (Auto-fix)
```bash
# Automatically fix coding standard violations
../moodle-plugin-ci/vendor/bin/phpcbf --standard=moodle ./file.php
```

### 4. Available Commands
Run without arguments to see all available commands:
```bash
../moodle-plugin-ci/bin/moodle-plugin-ci
```

## Key Features

### Code Quality Tools Included:
- **PHP CodeSniffer** - Detects violations of coding standards
- **PHP Code Beautifier** - Automatically fixes many coding standard issues
- **Mustache Linter** - Validates Mustache templates
- **PHPUnit** - Unit testing framework
- **PHPDoc Checker** - Documentation validation
- **JavaScript Linting** - ESLint integration
- **Moodle Coding Standards** - Pre-configured Moodle-specific rules

### Supported Checks:
- PHP syntax and coding standards
- Mustache template validation
- JavaScript linting
- PHPDoc documentation
- Database schema validation
- Plugin structure validation

## Directory Structure
```
/workspace/
├── moodle-plugin-ci.phar          # PHAR version (alternative)
├── composer.phar                  # Composer binary
└── ../moodle-plugin-ci/           # Composer installation
    ├── bin/
    │   └── moodle-plugin-ci       # Main executable
    ├── vendor/
    │   └── bin/
    │       ├── phpcs              # PHP CodeSniffer
    │       └── phpcbf             # PHP Code Beautifier
    └── src/                       # Source code
```

## Configuration

### PHP CodeSniffer Standards
The installation includes pre-configured Moodle coding standards:
- `moodle` - Main Moodle coding standard
- `phpcompatibility` - PHP compatibility checks
- `phpcsextra` - Additional PHP checks

### Custom Configuration
You can create custom configuration files for your specific needs:
- `.phpcs.xml` - PHP CodeSniffer configuration
- `phpunit.xml` - PHPUnit configuration
- `.eslintrc.js` - ESLint configuration

## Best Practices

1. **Run checks before committing:**
   ```bash
   ../moodle-plugin-ci/vendor/bin/phpcs --standard=moodle your-plugin/
   ```

2. **Use auto-fix when possible:**
   ```bash
   ../moodle-plugin-ci/vendor/bin/phpcbf --standard=moodle your-plugin/
   ```

3. **Integrate with your development workflow:**
   - Add to pre-commit hooks
   - Include in continuous integration
   - Run regularly during development

## Troubleshooting

### Common Issues:
1. **Permission denied:** Ensure proper file permissions
2. **Memory limits:** Increase PHP memory limit if needed
3. **Path issues:** Use absolute paths when in doubt

### Getting Help:
- Check the official documentation: https://moodlehq.github.io/moodle-plugin-ci/
- GitHub repository: https://github.com/moodlehq/moodle-plugin-ci
- Moodle developer forums

## Conclusion

Your moodle-plugin-ci environment is now fully set up and ready to use. You can validate your Moodle plugins and code using either the Composer installation or the PHAR package, depending on your preference and workflow requirements.

The tools are configured with Moodle-specific coding standards and will help ensure your code meets the quality requirements for Moodle plugin development.