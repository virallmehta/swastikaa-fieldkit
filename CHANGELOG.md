# Changelog

All notable changes to Swastikaa Fieldkit are documented here.
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [1.0.0] — 2026-03-10

### Added

**Field Types (18 total)**
- Text — single line input
- Textarea — multi-line input
- Number — numeric input with min/max/step
- Range — slider with min/max/step
- Email — email input with validation
- URL — URL input with validation
- Color — color picker
- Date — date picker
- DateTime — date and time picker
- Time — time picker
- Checkbox — single toggle or multi-choice
- Radio — radio button group
- Select — dropdown
- True/False — toggle switch
- Image — single image via media library
- File — file upload via media library
- Gallery — multiple images via media library
- WYSIWYG — WordPress rich text editor

**Field Group Builder**
- Create and manage field groups via custom post type `swfk_field_group`
- Add, remove, duplicate, and reorder fields via drag and drop
- Per-field settings: label, name, type, required, default, placeholder, instructions
- Choices configuration for Checkbox, Radio, Select (`value : Label` format)
- Min / Max / Step for Number and Range fields

**Location Rules**
- Attach field groups to post types, taxonomies, or user profiles
- OR logic — any matching rule shows the group
- Supports all registered public post types and taxonomies

**Runtime Rendering**
- Post edit screen metabox
- Taxonomy term add/edit form
- User profile/edit form
- Compatible with Classic Editor and Gutenberg

**Storage**
- Post meta — `SWFK_Post_Meta_Storage`
- Term meta — `SWFK_Term_Meta_Storage`
- User meta — `SWFK_User_Meta_Storage`

**REST API**
- All custom fields exposed via WordPress REST API
- Correct schema types: string, number, boolean, array
- Read and write with capability checks

**Helper Functions**
- `swfk_get_field()` / `swfk_the_field()`
- `swfk_get_image()` / `swfk_get_file()` / `swfk_get_gallery()`
- `swfk_get_term_field()` / `swfk_the_term_field()`
- `swfk_get_user_field()` / `swfk_the_user_field()`

---

## [2.0.0] — Planned

### Planned
- Repeater field with sub-field builder UI
- Gutenberg sidebar panel integration
- Options pages support
- Conditional logic (show/hide fields based on values)
- Flexible content field
- Relationship field
