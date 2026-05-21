# Custom Code

A minimal WordPress plugin boilerplate for organizing custom shortcodes and snippets without relying on third-party code injection plugins.

Built as a clean alternative to dumping everything into `functions.php`, while avoiding the overhead of a full code-snippets plugin.

## Why this exists

WordPress developers often face the same dilemma:

- Drop custom code into the theme's `functions.php` → gets wiped on theme updates, lost when switching themes.
- Install a snippet manager plugin → adds another dependency, another admin UI, and another vendor lock-in.
- Build a custom plugin from scratch every time → repetitive boilerplate work.

This is the middle ground: a tiny plugin that **auto-loads any PHP file you drop into its folders**, with zero configuration. Add a new shortcode? Create a file. Add a new snippet? Create a file. No need to touch the main plugin file.

## Folder structure
custom-code/
├── custom-code.php       # Plugin entry point with autoloader
├── shortcodes/           # Custom shortcodes ([cc_example])
└── snippets/             # Standalone code (GTM, redirects, filters, etc.)

Each PHP file inside `/shortcodes/` and `/snippets/` is loaded automatically on plugin activation. No manual `require_once` statements needed.

## What's included

- **Autoloader**: drops any `.php` file into `/shortcodes/` or `/snippets/` and it loads automatically.
- **GTM snippet boilerplate** (`snippets/gtm.php`): pre-built Google Tag Manager integration with a master on/off switch. Configure your ID, flip the switch, done.
- **Master switch pattern**: each snippet can be enabled or disabled with a single constant at the top of its file, no need to delete or move files around.

## Usage

### Install

1. Clone or download this repository.
2. Rename the folder to something unique to avoid conflicts with public plugins (e.g. `yourbrand-custom-code`).
3. Upload to `/wp-content/plugins/`.
4. Activate from the WordPress admin.

**Important**: do not keep the folder name as `custom-code` in production. A public plugin with that exact slug exists on the WordPress.org repository, and WordPress will prompt you to "update" it, which would overwrite your code. Use a prefix unique to your project.

### Adding a new shortcode

Create a file inside `/shortcodes/`:

```php
<?php
// shortcodes/hello.php

if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('cc_hello', function ($atts) {
    $atts = shortcode_atts(['name' => 'World'], $atts);
    return 'Hello, ' . esc_html($atts['name']) . '!';
});
```

Use it anywhere: `[cc_hello name="Tebiko"]`

### Adding a new snippet

Create a file inside `/snippets/`:

```php
<?php
// snippets/disable-comments.php

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_init', function () {
    // Disable support for comments on all post types
    foreach (get_post_types() as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
        }
    }
});
```

That's it. No registration step, no configuration file.

### Enabling GTM

Open `snippets/gtm.php`:

```php
define('CC_GTM_ENABLED', false);    // change to true
define('CC_GTM_ID', 'GTM-XXXXXX');  // replace with your container ID
```

Set `CC_GTM_ENABLED` to `true` and replace the placeholder ID with your real one. The script will inject in `<head>` and the `<noscript>` fallback in `<body>`.

### Disabling a snippet without deleting it

Two options:

**Option A — Master switch (recommended)**

Add a constant at the top of any snippet file:

```php
define('CC_FEATURE_ENABLED', false);
if (!CC_FEATURE_ENABLED) return;

// rest of the snippet code
```

Flip to `true` when you want it active.

**Option B — Rename the file**

The autoloader only picks up files ending in `.php`. Rename `gtm.php` to `gtm.php.off` and it stops loading immediately. Useful for emergency deactivation via FTP.

## Conventions

- All function names, hooks, and constants are prefixed with `cc_` (Custom Code) to avoid collisions.
- Code, comments, and variable names are in English regardless of the site's language.
- Each file starts with `if (!defined('ABSPATH')) exit;` to prevent direct access.
- Output values are escaped using WordPress core functions (`esc_html`, `esc_attr`, `esc_js`, etc.).

## Why not use Third Party Plugins on Your site?

Those plugins are great for non-developers or teams who need a GUI to manage snippets. This boilerplate is for developers who prefer:

- Editing in their own IDE with proper syntax highlighting, search, and version control.
- Keeping snippets in Git instead of a database.
- Zero admin UI overhead.
- No vendor dependency.

If you need a UI, use Code Snippets. If you don't, this is lighter and cleaner.

## Requirements

- WordPress 6.0+
- PHP 7.4+

## License

GPL v2 or later.

## Credits

Built and maintained by [Tebiko](https://tebiko.com).
