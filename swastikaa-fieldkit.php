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
define( 'SWFK_PLUGIN_FILE',  __FILE__ );

/** @since 1.0.0 */
define( 'SWFK_PLUGIN_DIR',   plugin_dir_path( __FILE__ ) );

/** @since 1.0.0 */
define( 'SWFK_PLUGIN_URL',   plugin_dir_url( __FILE__ ) );

/** @since 1.0.0 */
define( 'SWFK_INCLUDES_DIR', SWFK_PLUGIN_DIR . 'includes/' );

// ── Interfaces ────────────────────────────────────────────────────────────────

require_once SWFK_PLUGIN_DIR . 'includes/interface/interface-swfk-context.php';
require_once SWFK_PLUGIN_DIR . 'includes/interface/interface-swfk-storage.php';
require_once SWFK_PLUGIN_DIR . 'includes/interface/interface-swfk-location-rule.php';

// ── Storage ───────────────────────────────────────────────────────────────────

require_once SWFK_PLUGIN_DIR . 'includes/storage/class-swfk-post-meta-storage.php';
require_once SWFK_PLUGIN_DIR . 'includes/storage/class-swfk-term-meta-storage.php';
require_once SWFK_PLUGIN_DIR . 'includes/storage/class-swfk-user-meta-storage.php';
require_once SWFK_PLUGIN_DIR . 'includes/storage/class-swfk-options-storage.php';

// ── Context ───────────────────────────────────────────────────────────────────

require_once SWFK_PLUGIN_DIR . 'includes/context/class-swfk-post-context.php';
require_once SWFK_PLUGIN_DIR . 'includes/context/class-swfk-term-context.php';
require_once SWFK_PLUGIN_DIR . 'includes/context/class-swfk-user-context.php';
require_once SWFK_PLUGIN_DIR . 'includes/context/class-swfk-options-context.php';

// ── Core ──────────────────────────────────────────────────────────────────────

require_once SWFK_PLUGIN_DIR . 'includes/core/class-swfk-field-registry.php';
require_once SWFK_PLUGIN_DIR . 'includes/core/class-swfk-assets-manager.php';
require_once SWFK_PLUGIN_DIR . 'includes/core/class-swfk-field-loader.php';
require_once SWFK_PLUGIN_DIR . 'includes/core/class-swfk-field-base.php';
require_once SWFK_PLUGIN_DIR . 'includes/core/class-swfk-template-registry.php';
require_once SWFK_PLUGIN_DIR . 'includes/core/class-swfk-template-loader.php';

// ── Rules ─────────────────────────────────────────────────────────────────────

require_once SWFK_PLUGIN_DIR . 'includes/rules/class-swfk-general-rule.php';

// ── Plugin & Admin ────────────────────────────────────────────────────────────

require_once SWFK_PLUGIN_DIR . 'includes/core/class-swfk-field-group-repository.php';
require_once SWFK_PLUGIN_DIR . 'includes/class-swfk-plugin.php';
require_once SWFK_PLUGIN_DIR . 'admin/class-swfk-admin.php';
require_once SWFK_PLUGIN_DIR . 'helpers/swfk-helpers.php';
require_once SWFK_PLUGIN_DIR . 'includes/core/class-swfk-rest-api.php';

// ── Bootstrap ─────────────────────────────────────────────────────────────────

SWFK_Plugin();
