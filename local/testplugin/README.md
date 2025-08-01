# Test Plugin (local_testplugin)

A simple test plugin for Moodle to test CI/CD workflows.

## Features

- Basic plugin structure
- Settings page
- Simple functionality demonstration
- No PHPUnit tests (as requested)

## Installation

1. Copy the plugin to `local/testplugin/`
2. Visit Site Administration to complete the installation
3. Configure settings under Site Administration > Plugins > Local plugins > Test Plugin

## Usage

- Access the plugin at `/local/testplugin/index.php`
- Configure settings in Site Administration
- View plugin information and status

## Purpose

This plugin is designed for testing:
- CI/CD workflows
- Plugin detection systems
- Deployment processes
- Code quality checks

## Files Structure

```
local/testplugin/
├── version.php          # Plugin version and metadata
├── lang/en/local_testplugin.php  # Language strings
├── lib.php              # Library functions
├── index.php            # Main plugin page
├── settings.php         # Admin settings
├── db/access.php        # Capability definitions
└── README.md            # This file
```

## License

GPL v3 or later