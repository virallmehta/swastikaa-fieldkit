<?php
/**
 * Plugin Name:       Swastikaa Fieldkit
 * Plugin URI:        https://github.com/virallmehta/swastikaa-fieldkit
 * Description:       The most flexible WordPress fields framework for Posts, Terms, Users, and Options — by Viral Mehta
 * Version:           1.0.0
 * Requires at least: 6.0
 * Tested up to:      6.9
 * Requires PHP:      8.0
 * Author:            Viral Mehta
 * Author URI:        https://profiles.wordpress.org/viralmehta/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       swastikaa-fieldkit
 *
 * @package           SwastikaaFieldkit
 * @since             1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ── Plugin Constants ──────────────────────────────────────────────────────────

/** @since 1.0.0 */
define( 'SWFK_VERSION',      '1.0.0' );

/** @since 1.0.0 */
define( 'SNFS_PLUGIN_FILE',  __FILE__ );

/** @since 1.0.0 */
define( 'SNFS_PLUGIN_DIR',   plugin_dir_path( __FILE__ ) );

/** @since 1.0.0 */
define( 'SNFS_PLUGIN_URL',   plugin_dir_url( __FILE__ ) );

/** @since 1.0.0 */
define( 'SNFS_INCLUDES_DIR', SNFS_PLUGIN_DIR . 'includes/' );

// ── Interfaces ────────────────────────────────────────────────────────────────

require_once SNFS_PLUGIN_DIR . 'includes/interface/interface-snfs-context.php';
require_once SNFS_PLUGIN_DIR . 'includes/interface/interface-snfs-storage.php';
require_once SNFS_PLUGIN_DIR . 'includes/interface/interface-snfs-location-rule.php';

// ── Storage ───────────────────────────────────────────────────────────────────

require_once SNFS_PLUGIN_DIR . 'includes/storage/class-snfs-post-meta-storage.php';
require_once SNFS_PLUGIN_DIR . 'includes/storage/class-snfs-term-meta-storage.php';
require_once SNFS_PLUGIN_DIR . 'includes/storage/class-snfs-user-meta-storage.php';
require_once SNFS_PLUGIN_DIR . 'includes/storage/class-snfs-options-storage.php';

// ── Context ───────────────────────────────────────────────────────────────────

require_once SNFS_PLUGIN_DIR . 'includes/context/class-snfs-post-context.php';
require_once SNFS_PLUGIN_DIR . 'includes/context/class-snfs-term-context.php';
require_once SNFS_PLUGIN_DIR . 'includes/context/class-snfs-user-context.php';
require_once SNFS_PLUGIN_DIR . 'includes/context/class-snfs-options-context.php';

// ── Core ──────────────────────────────────────────────────────────────────────

require_once SNFS_PLUGIN_DIR . 'includes/core/class-snfs-field-registry.php';
require_once SNFS_PLUGIN_DIR . 'includes/core/class-snfs-assets-manager.php';
require_once SNFS_PLUGIN_DIR . 'includes/core/class-snfs-field-loader.php';
require_once SNFS_PLUGIN_DIR . 'includes/core/class-snfs-field-base.php';
require_once SNFS_PLUGIN_DIR . 'includes/core/class-snfs-template-registry.php';
require_once SNFS_PLUGIN_DIR . 'includes/core/class-snfs-template-loader.php';

// ── Rules ─────────────────────────────────────────────────────────────────────

require_once SNFS_PLUGIN_DIR . 'includes/rules/class-snfs-general-rule.php';

// ── Plugin & Admin ────────────────────────────────────────────────────────────

require_once SNFS_PLUGIN_DIR . 'includes/core/class-snfs-field-group-repository.php';
require_once SNFS_PLUGIN_DIR . 'includes/class-snfs-plugin.php';
require_once SNFS_PLUGIN_DIR . 'admin/class-snfs-admin.php';
require_once SNFS_PLUGIN_DIR . 'helpers/snfs-helpers.php';
require_once SNFS_PLUGIN_DIR . 'includes/core/class-snfs-rest-api.php';

// ── Bootstrap ─────────────────────────────────────────────────────────────────

SNFS_Plugin();
