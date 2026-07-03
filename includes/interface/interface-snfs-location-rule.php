<?php
/**
 * Location rule interface. All rule evaluators must implement this interface,
 * providing a match() method to test a context against a set of rules.
 *
 * @package SwastiNexusFieldsStudio
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

interface SNFS_Location_Rule_Interface {
    /**
     * Check if context matches rule.
     *
     * @param SNFS_Context_Interface $context Current context (post/user/etc).
     * @param array $rule Rule config: ['param' => 'post_type', 'operator' => '==', 'value' => 'post'].
     * @return bool Matches?
     */
    public function match( SNFS_Context_Interface $context, array $rule ): bool;
}
