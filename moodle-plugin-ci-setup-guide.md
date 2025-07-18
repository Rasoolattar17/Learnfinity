# Moodle Plugin CI - Local Development Setup Guide

## Overview

This guide demonstrates how to set up and use moodle-plugin-ci in your local development environment. Moodle Plugin CI is a comprehensive tool that provides various code quality checks and automated fixes for Moodle plugins and core code.

## Installation Methods

### Method 1: Using Composer (Recommended for full functionality)

If you're outside the Moodle directory, install using Composer:

```bash
composer create-project moodlehq/moodle-plugin-ci ../moodle-plugin-ci ^4
```

Then use it as:
```bash
../moodle-plugin-ci/bin/moodle-plugin-ci [command] [options]
```

### Method 2: Using PHAR file (Simpler setup)

Download the PHAR file directly to your Moodle directory:

```bash
wget https://github.com/moodlehq/moodle-plugin-ci/releases/download/4.5.8/moodle-plugin-ci.phar
```

Then use it as:
```bash
php moodle-plugin-ci.phar [command] [options]
```

## Available Commands

The following commands are available in moodle-plugin-ci:

### Code Quality Tools
- `phpcs` - Run Moodle CodeSniffer standard on a plugin
- `phpcbf` - Run Code Beautifier and Fixer on a plugin
- `phplint` - Run PHP Lint on a plugin
- `phpmd` - Run PHP Mess Detector on a plugin
- `phpdoc` - Run Moodle PHPDoc Checker on a plugin

### Template and Frontend Tools
- `mustache` - Run Mustache Lint on a plugin
- `grunt` - Run Grunt task on a plugin

### Testing Tools
- `phpunit` - Run PHPUnit on a plugin
- `behat` - Run Behat on a plugin

### Validation Tools
- `validate` - Validate a plugin
- `savepoints` - Check upgrade savepoints

### Utility Commands
- `install` - Install everything required for CI testing
- `parallel` - Run all tests and analysis against a plugin
- `add-config` - Add a line to the Moodle config.php file
- `add-plugin` - Queue up an additional plugin to be installed

## Practical Examples

### 1. Mustache Template Validation

```bash
php moodle-plugin-ci.phar mustache ./mod/forum/
```

**Output:**
```
 RUN  Mustache Lint on mod_forum
/workspace/mod/forum/templates/quick_search_form.mustache - OK: Mustache rendered html successfully
/workspace/mod/forum/templates/single_discussion_list.mustache - OK: Mustache rendered html successfully
...
```

### 2. PHP CodeSniffer Analysis

```bash
php moodle-plugin-ci.phar phpcs ./mod/forum/
```

**Sample Results:**
- Found **42 errors** and **97 warnings** in `/mod/forum/externallib.php`
- Found **39 errors** and **69 warnings** in `/mod/forum/locallib.php`
- Found **40 errors** and **27 warnings** in `/mod/forum/rsslib.php`

**Common Issues Detected:**
- Short array syntax violations (`array()` vs `[]`)
- Missing trailing commas in multi-line arrays
- Logical operator violations (`and` vs `&&`, `or` vs `||`)
- Line length violations (> 132 characters)
- Missing docblock comments
- Incorrect indentation

### 3. PHP Code Beautifier and Fixer (PHPCBF)

```bash
php moodle-plugin-ci.phar phpcbf ./mod/forum/
```

**Results:**
- **2,850 errors were automatically fixed** across **152 files**
- Processing time: ~37 seconds
- Memory usage: ~96MB

**Types of Fixes Applied:**
- Converted long array syntax to short syntax
- Added missing trailing commas
- Fixed spacing around operators
- Corrected indentation
- Fixed comment formatting

## Using Individual Tools

### Direct PHP CodeSniffer Usage

If you installed via Composer, you can use the underlying tools directly:

```bash
# For individual files (requires Composer installation)
../moodle-plugin-ci/vendor/bin/phpcs ./index.php

# For automatic fixes
../moodle-plugin-ci/vendor/bin/phpcbf ./index.php
```

## Best Practices

1. **Run checks before committing**: Use moodle-plugin-ci to validate your code before pushing changes
2. **Fix automatically fixable issues**: Use `phpcbf` to automatically resolve common coding standard violations
3. **Review manual fixes**: Some issues require manual intervention and code review
4. **Use in CI/CD pipelines**: Integrate these tools into your continuous integration workflow
5. **Regular validation**: Run checks regularly during development, not just before release

## Performance Considerations

- **Large plugins**: Analysis time increases with plugin size
- **Memory usage**: Large plugins may require more memory (up to 100MB+)
- **Parallel processing**: Use the `parallel` command to run multiple checks simultaneously
- **Incremental checks**: Consider running checks only on changed files for faster feedback

## Troubleshooting

### Common Issues

1. **Permission errors**: Ensure proper file permissions for the PHAR file
2. **Memory limits**: Increase PHP memory limit for large plugins
3. **Path issues**: Use absolute paths when in doubt
4. **Plugin structure**: Ensure your plugin follows Moodle's standard structure

### System Requirements

- PHP 7.4 or higher
- Composer (for full installation)
- Sufficient memory (recommended: 512MB+)
- Write permissions for temporary files

## Integration with Development Workflow

### Pre-commit Hook Example

```bash
#!/bin/bash
# Run basic checks before commit
php moodle-plugin-ci.phar phpcs ./path/to/your/plugin/
if [ $? -ne 0 ]; then
    echo "Code style issues found. Please fix before committing."
    exit 1
fi
```

### IDE Integration

Most IDEs can be configured to run these tools automatically or on-demand, providing real-time feedback during development.

## Conclusion

Moodle Plugin CI provides a comprehensive suite of tools for maintaining code quality in Moodle development. By integrating these tools into your development workflow, you can:

- Ensure consistent code style across your project
- Catch potential issues early in development
- Automate many common code quality fixes
- Maintain compliance with Moodle coding standards

The combination of automated fixes (via PHPCBF) and detailed analysis (via PHPCS) makes it an invaluable tool for Moodle developers working in local environments.