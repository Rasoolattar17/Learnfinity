# Moodle CI: Comprehensive Guide

## What is Moodle CI?

Moodle CI (Continuous Integration) is a comprehensive testing and code analysis toolkit designed specifically for Moodle plugin development. It facilitates automated testing and code quality checks for Moodle plugins in various CI environments like GitHub Actions and Travis CI.

## Key Components

### 1. Moodle Plugin CI (moodle-plugin-ci)
- **Repository**: https://github.com/moodlehq/moodle-plugin-ci
- **Documentation**: https://moodlehq.github.io/moodle-plugin-ci/
- **Purpose**: Main tool that orchestrates testing and code analysis
- **Current Version**: 4.x (requires PHP 7.4+ and Moodle 3.8.3+)

### 2. Moodle CI Runner (moodle-ci-runner)
- **Repository**: https://github.com/moodlehq/moodle-ci-runner
- **Purpose**: Docker-based test runner for consistent CI environment
- **Features**: Modular design with jobs, modules, and stages

### 3. Catalyst Moodle Workflows
- **Repository**: https://github.com/catalyst/catalyst-moodle-workflows
- **Purpose**: Reusable GitHub Actions workflows for Moodle plugins
- **Features**: Simplified CI setup with pre-configured workflows

## Supported Testing Frameworks and Tools

Moodle CI supports the following testing and analysis tools:

### Testing Frameworks
- **PHPUnit**: Unit testing framework for PHP
- **Behat**: Behavior-driven development testing

### Code Analysis Tools
- **Moodle Code Checker**: Ensures code follows Moodle coding standards
- **Moodle PHPdoc check**: Validates PHPdoc documentation
- **Mustache Linting**: Checks Mustache template syntax
- **Grunt tasks**: JavaScript/CSS build tasks
- **PHP Linting**: Basic PHP syntax checking
- **PHP Copy/Paste Detector** (DEPRECATED): Detects code duplication
- **PHP Mess Detector**: Identifies potential code quality issues

## How It Works

### 1. Automation Benefits
- **Saves Time**: Eliminates manual testing setup for every code change
- **Consistency**: Ensures all tests run the same way every time
- **Pull Request Safety**: Provides confidence when accepting contributions
- **Multi-Environment Testing**: Tests against multiple PHP versions, databases, and Moodle versions

### 2. CI Integration
- **GitHub Actions**: Primary supported CI platform
- **Travis CI**: Legacy support (deprecated as of v4.5.8, will be removed in v5.0.0)
- **Other CI Services**: Community contributions for other platforms

## Getting Started

### GitHub Actions Setup

1. **Copy Configuration File**
   ```bash
   # Copy the template to your plugin repository
   cp gha.dist.yml .github/workflows/moodle-ci.yml
   ```

2. **Configure Your Plugin**
   - Ensure your plugin has a `version.php` file
   - Define `$plugin->component` in version.php
   - Set supported Moodle versions in `$plugin->supported`

3. **Basic Workflow Example**
   ```yaml
   name: ci
   on: [push, pull_request]
   
   jobs:
     ci:
       uses: catalyst/catalyst-moodle-workflows/.github/workflows/ci.yml@main
       with:
         # Optional: disable specific checks
         disable_behat: true
         disable_grunt: true
   ```

### Local Development Usage

You can also run Moodle CI locally for development:

```bash
# Install via Composer
php composer.phar create-project moodlehq/moodle-plugin-ci ../moodle-plugin-ci ^4

# Run specific checks
../moodle-plugin-ci/bin/moodle-plugin-ci mustache ./mod/forum/
../moodle-plugin-ci/bin/moodle-plugin-ci phpunit ./mod/forum/
```

## Key Features

### 1. Version Matrix Testing
- Tests against multiple Moodle versions automatically
- Supports testing from Moodle 3.8.3 to latest development version
- Configurable via `$plugin->supported` in version.php

### 2. Database Support
- PostgreSQL (default)
- MySQL/MariaDB
- Oracle
- Microsoft SQL Server
- SQLite

### 3. Browser Testing (Behat)
- Chrome (default)
- Firefox
- Headless mode support
- Mobile app testing support

### 4. Code Quality Checks
- **PHP CodeSniffer**: Enforces Moodle coding standards
- **PHPdoc Validation**: Ensures proper documentation
- **Mustache Linting**: Template validation
- **JavaScript/CSS Linting**: Via Grunt tasks

## Advanced Configuration

### Customization Options
```yaml
with:
  codechecker_max_warnings: 0  # Fail on warnings
  extra_plugin_runners: 'moodle-plugin-ci add-plugin catalyst/moodle-local_aws'
  disable_behat: true
  disable_phpdoc: true
  disable_phpcs: true
  disable_phplint: true
  disable_phpunit: true
  disable_grunt: true
  disable_mustache: true
  min_php: '7.4'
  ignore_paths: 'vendor,node_modules'
```

### Dependencies Management
```yaml
extra_plugin_runners:
  moodle-plugin-ci add-plugin danmarsden/moodle-mod_attendance --branch MOODLE_401_STABLE
```

## Historical Context

### Evolution
- **Original Creator**: MoodleRooms/Blackboard
- **Current Maintainer**: Moodle HQ (since 2020)
- **Key Contributors**: Mark Nielsen, Eloy Lafuente, Tim Hunt, and many others

### Version History
- **Version 1-3**: Legacy versions (no longer maintained)
- **Version 4**: Current version with modern PHP and Moodle support
- **Version 5**: Planned future version (will remove Travis CI support)

## Related Tools

### 1. Moodle Code Checker (local_codechecker)
- **Repository**: https://github.com/moodlehq/moodle-local_codechecker
- **Purpose**: Web UI for running code style checks
- **Integration**: Used by moodle-plugin-ci for code validation

### 2. Moodle PHPdoc Checker (local_moodlecheck)
- **Repository**: https://github.com/moodlehq/moodle-local_moodlecheck
- **Purpose**: Validates PHPdoc documentation style
- **Status**: Being deprecated in favor of moodle-cs integration

### 3. Moodle CS (Coding Standards)
- **Repository**: https://github.com/moodlehq/moodle-cs
- **Purpose**: Modern PHP CodeSniffer rules for Moodle
- **Integration**: Used by code checker tools

## Best Practices

### 1. CI Configuration
- Use the latest version of moodle-plugin-ci
- Configure appropriate PHP and Moodle version ranges
- Enable all relevant checks for your plugin type
- Add CI status badges to your README

### 2. Code Quality
- Write comprehensive PHPUnit tests
- Include Behat tests for user interactions
- Follow Moodle coding standards consistently
- Document your code with proper PHPdoc

### 3. Maintenance
- Keep your CI configuration updated
- Monitor test results regularly
- Fix failing tests promptly
- Update dependencies when new versions are available

## Troubleshooting

### Common Issues
1. **Stale Grunt Files**: Rebuild on the highest supported Moodle version
2. **Database Compatibility**: Test against all supported database types
3. **PHP Version Conflicts**: Ensure compatibility across PHP versions
4. **Memory Limits**: Increase memory limits for large test suites

### Getting Help
- **Documentation**: https://moodlehq.github.io/moodle-plugin-ci/
- **Issues**: Report bugs on GitHub repositories
- **Community**: Moodle developer forums and community channels

## Conclusion

Moodle CI is an essential tool for maintaining high-quality Moodle plugins. It provides comprehensive testing and code analysis capabilities that help developers:

- Maintain code quality standards
- Ensure compatibility across Moodle versions
- Automate testing processes
- Collaborate more effectively
- Deliver reliable plugins to the Moodle community

By implementing Moodle CI in your plugin development workflow, you can significantly improve code quality, reduce bugs, and streamline the development process.