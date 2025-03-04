<?php

namespace MeuMouse\Flexify_Checkout\Recovery_Carts\Core;

// Exit if accessed directly.
defined('ABSPATH') || exit;

/**
 * Handle with replacement text placeholders
 * 
 * @since 1.0.0
 * @package MeuMouse.com
 */
class Placeholders {

    /**
     * Replaces placeholders in a message with actual values
     *
     * @since 1.0.0
     * @param string $message | The message containing placeholders
     * @param array $values | An associative array of placeholders and their corresponding values
     * @return string The message with placeholders replaced by actual values
     */
    public static function replace_placeholders( $message, $values = array() ) {
        // Get available placeholders
        $placeholders = Helpers::get_message_placeholders();

        // Loop through placeholders and replace them with values if provided
        foreach ( $placeholders as $placeholder => $description ) {
            if ( isset( $values[$placeholder] ) ) {
                $message = str_replace( $placeholder, $values[$placeholder], $message );
            }
        }

        return $message;
    }
}