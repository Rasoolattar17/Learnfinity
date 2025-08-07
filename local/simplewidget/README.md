# Simple Widget Plugin

A simple demonstration plugin for Moodle that shows basic plugin functionality.

## Features

- Displays current user information
- Shows site statistics (course count, user count)
- Displays current time
- Responsive design with CSS styling
- Proper Moodle coding standards compliance

## Installation

1. Copy the `simplewidget` folder to your Moodle's `local/` directory
2. Visit Site Administration > Notifications to install the plugin
3. The plugin will be automatically installed

## Usage

1. Navigate to `/local/simplewidget/index.php` in your browser
2. You must have the `local/simplewidget:view` capability to access the plugin
3. By default, managers and teachers have this capability

## Capabilities

- `local/simplewidget:view` - Allows users to view the simple widget

## Files Structure

```
local/simplewidget/
├── version.php          # Plugin version information
├── lib.php              # Library functions
├── index.php            # Main plugin page
├── styles.css           # CSS styling
├── db/
│   └── access.php       # Capability definitions
├── lang/
│   └── en/
│       └── local_simplewidget.php  # Language strings
└── README.md            # This file
```

## Customization

You can customize the plugin by:

1. Modifying the language strings in `lang/en/local_simplewidget.php`
2. Updating the styling in `styles.css`
3. Adding new functionality in `lib.php`
4. Extending the main page in `index.php`

## Requirements

- Moodle 4.3 or later
- PHP 8.0 or later

## License

This plugin is licensed under the GNU General Public License v3.0.

## Support

For support or questions, please refer to the Moodle documentation or community forums. 