<?php
/**
 * Evaluates field group location rules against the current context.
 * Determines whether a field group should be displayed for a given post type,
 * taxonomy, user profile, or options page.
 *
 * @package Swastikaa-Fieldkit
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SWFK_General_Rule implements SWFK_Location_Rule_Interface {

    /**
     * Returns true if ANY rule in the array matches the context (OR logic).
     *
     * @param SWFK_Context_Interface $context Current screen context.
     * @param array                $rules   Array of rule arrays from saved meta.
     */
    public function match( SWFK_Context_Interface $context, array $rules ): bool {
        if ( empty( $rules ) ) {
            return false;
        }

        foreach ( $rules as $rule ) {
            if ( ! is_array( $rule ) || empty( $rule['type'] ) ) {
                continue;
            }
            if ( $this->match_single( $context, $rule ) ) {
                return true;
            }
        }

        return false;
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function match_single( SWFK_Context_Interface $context, array $rule ): bool {
        $rule_type = $rule['type']     ?? '';
        $operator  = $rule['operator'] ?? 'is';
        $value     = $rule['value']    ?? '';

        /**
         * Get what the context "is" for this rule type.
         *
         * For 'post_type' and 'taxonomy' and 'options_page' rules:
         *   context->get_type() already returns the right slug — the rule value
         *   is set to that same slug in the UI dropdown.
         *
         * For 'user_profile' rules:
         *   SWFK_User_Context::get_type() returns 'user'.
         *   The UI saves value = 'all' (the only option for user profile).
         *   So we just check the context IS a user context, not match on value.
         */
        $current = match ( $rule_type ) {
            'post_type'    => $context->get_type(),
            'taxonomy'     => $context->get_type(),
            'options_page' => $context->get_type(),
            'user_profile' => $context instanceof SWFK_User_Context ? 'user' : '__not_user__',
            default        => '',
        };

        /**
         * Special case: user_profile rules.
         * The rule value in UI is 'all' (meaning any user profile).
         * So we just check whether current context IS a user context.
         */
        if ( $rule_type === 'user_profile' ) {
            $is_user = ( $context instanceof SWFK_User_Context );
            return $operator === 'is' ? $is_user : ! $is_user;
        }

        return match ( $operator ) {
            'is'     => $current === $value,
            'is_not' => $current !== $value,
            default  => false,
        };
    }
}
