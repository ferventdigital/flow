# Flow

A lightweight Craft CMS plugin that intelligently manages queue workers, preventing server overload by limiting concurrent jobs and ensuring smooth task execution.

## Requirements

This plugin requires Craft CMS 4.10.0 or later and PHP 8.0.2 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “Flow”. Then press “Install”.

### With Composer

Open your terminal and run the following commands:

```bash
# Go to the project directory
cd /path/to/my-project.test

# Tell Composer to load the plugin
composer require ferventdigital/flow

# Tell Craft to install the plugin
./craft plugin/install flow
```

## Optional Configuration

Flow works out of the box without requiring any configuration. By default, the plugin automatically detects the PHP binary and sets a maximum of 2 concurrent workers.

However, if you want to override these defaults, you can define environment variables in your .env file:

```bash
FLOW_PHP_BINARY=/usr/bin/php
FLOW_MAX_WORKERS=5
```

- **`FLOW_PHP_BINARY`**: Specifies the PHP binary path to be used for executing queue jobs. Adjust this path based on your server setup.
- **`FLOW_MAX_WORKERS`**: Defines the maximum number of workers that can run concurrently. The default is 2.

### Applying Configuration Changes

After making changes, clear Craft’s cache to apply the new configuration:

```bash
./craft clear-caches
```

## Testing the Queue

You can test Flow’s queue management functionality within the Craft CMS control panel:

1. Navigate to **Utilities** > **Flow Test**.
2. Click the **Run test** button to initiate a test job.
3. Monitor how Flow manages the queue and worker execution.

This allows you to verify that the plugin is correctly handling job execution and respecting the configured worker limits.

