# Swastikaa Fieldkit

[![Version](https://img.shields.io/badge/version-1.0.0-blue)](https://github.com/virallmehta/swastikaa-fieldkit)
[![PHP](https://img.shields.io/badge/php-8.0%2B-purple)](https://www.php.net)
[![WordPress](https://img.shields.io/badge/wordpress-6.0%2B-21759b)](https://wordpress.org)
[![License](https://img.shields.io/github/license/virallmehta/swfk)](LICENSE)
[![Issues](https://img.shields.io/github/issues/virallmehta/swfk)](https://github.com/virallmehta/swfk/issues)
[![Last Commit](https://img.shields.io/github/last-commit/virallmehta/swfk)](https://github.com/virallmehta/swfk/commits/main)

A lightweight, developer-friendly custom fields plugin for WordPress — built by [Viral Mehta](https://profiles.wordpress.org/viralmehta/).

---

## Overview

Swastikaa Fieldkit lets you create custom field groups and attach them to any post type, taxonomy, or user profile — without writing a single line of PHP. It follows the same mental model as ACF but is built from scratch with a clean, modern architecture.

---

## Requirements

- WordPress 6.0+
- PHP 8.0+

---

## Installation

1. Upload the `swastikaa-fieldkit` folder to `/wp-content/plugins/`
2. Activate the plugin from **Plugins → Installed Plugins**
3. Go to **Swastikaa Fieldkit** in the admin menu to create your first field group

---

## Field Types

| Type         | Description                                  |
|--------------|----------------------------------------------|
| Text         | Single line text input                       |
| Textarea     | Multi-line text input                        |
| Number       | Numeric input with optional min/max/step     |
| Range        | Slider input with min/max/step               |
| Email        | Email address input with validation          |
| URL          | URL input with validation                    |
| Color        | Color picker                                 |
| Date         | Date picker                                  |
| Date Time    | Date and time picker                         |
| Time         | Time picker                                  |
| Checkbox     | Single toggle or multi-choice checkboxes     |
| Radio        | Radio button group                           |
| Select       | Dropdown select                              |
| True / False | Toggle switch                                |
| Image        | Single image via WordPress media library     |
| File         | File upload via WordPress media library      |
| Gallery      | Multiple images via WordPress media library  |
| WYSIWYG      | WordPress rich text editor                   |

---

## Creating a Field Group

1. Go to **Swastikaa Fieldkit → Add Post**
2. Enter a title for the field group e.g. "Property Details"
3. Click **+ Add Field** to add fields
4. Set **Location Rules** in the sidebar to choose where the group appears
5. Click **Publish**

---

## Location Rules

Location rules control where a field group is displayed. Rules use **OR logic** — if any rule matches, the group is shown.

| Rule Type    | Description                        |
|--------------|------------------------------------|
| Post Type    | Show on a specific post type       |
| Taxonomy     | Show on a specific taxonomy        |
| User Profile | Show on user profile/edit screens  |

---

## Helper Functions

### Posts

```php
// Get a field value
$value = swfk_get_field( 'field_name', $post_id );

// Echo a field value
swfk_the_field( 'field_name', $post_id );

// Get an image field (returns array with id, url, alt, width, height)
$image = swfk_get_image( 'field_name', $post_id, 'large' );

// Get a file field (returns array with id, url, title, filename)
$file = swfk_get_file( 'field_name', $post_id );

// Get a gallery field (returns array of image arrays)
$images = swfk_get_gallery( 'field_name', $post_id, 'full' );
```

### Taxonomy Terms

```php
// Get a term field value
$value = swfk_get_term_field( 'field_name', $term, $default );

// Echo a term field value
swfk_the_term_field( 'field_name', $term, $default );
```

### Users

```php
// Get a user field value
$value = swfk_get_user_field( 'field_name', $user, $default );

// Echo a user field value
swfk_the_user_field( 'field_name', $user, $default );
```

---

## REST API

All SwastiNexus Fields Studio custom fields are automatically exposed via the WordPress REST API.

**Example:**
```
GET /wp-json/wp/v2/properties/43
```

Response includes all `swfk_` prefixed fields:
```json
{
  "id": 43,
  "swfk_price": "5000000",
  "swfk_color": "#883535",
  "swfk_construction_type": ["old", "new"]
}
```

---

## Choices Format

For Checkbox, Radio, and Select fields, enter choices one per line in the format:

```
value : Label
```

**Example:**
```
old : Old
new : New
under_construction : Under Construction
```

---

## File Structure

```
swastikaa-fieldkit/
├── swastikaa-fieldkit.php              # Plugin bootstrap
├── admin/
│   └── class-swfk-admin.php       # Admin UI — field builder + runtime rendering
├── assets/
│   ├── css/admin.css
│   └── js/
│       ├── admin.js             # Field group builder JS
│       └── swfk-admin-fields.js   # Media field JS
├── fields/                      # One folder per field type
│   ├── text/
│   ├── checkbox/
│   └── ...
├── helpers/
│   └── swfk-helpers.php           # Template helper functions
└── includes/
    ├── class-swfk-plugin.php      # Plugin orchestrator
    ├── context/                 # Post / Term / User / Options contexts
    ├── core/                    # Registry, loader, repository, REST API
    ├── interface/               # PHP interfaces
    ├── rules/                   # Location rule matching
    └── storage/                 # Meta storage drivers
```

---

## Roadmap — v2 Community Edition

- Repeater field with sub-field builder
- Gutenberg sidebar panel
- Options pages
- Conditional logic
- Flexible content field
- Relationship field

---

## Contributing

Pull requests are welcome. For major changes please open an issue first.

- [Report a Bug](https://github.com/virallmehta/swfk/issues/new?template=bug-report.md)
- [Request a Feature](https://github.com/virallmehta/swfk/issues/new?template=feature-request.md)

---

## License

GPL v2 or later — see [LICENSE](LICENSE)

---

## Author

Built by [Viral Mehta](https://profiles.wordpress.org/viralmehta/)# swastikaa-fieldkit