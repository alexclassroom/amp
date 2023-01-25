<?php
/**
 * Trait WithoutBlockPreRendering.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Helpers;

use AMP_Options_Manager;
use AmpProject\AmpWP\Option;
use WP_Error;
use WP_User;

/**
 * Helper trait to help with setting up the test environment for block editor support.
 */
trait WithBlockEditorSupport {

	/**
	 * Setup test environment to ensure the correct result for ::supports_current_screen().
	 *
	 * @param bool   $post_type_uses_block_editor Whether the post type uses the block editor.
	 * @param bool   $post_type_supports_amp      Whether the post type supports AMP.
	 * @param string $post_type                   Post type ID.
	 */
	public function setup_environment( $post_type_uses_block_editor, $post_type_supports_amp, $post_type = 'foo' ) {
		if ( $post_type_uses_block_editor ) {
			$block_http_request = static function () {
				return new WP_Error( 'request_blocked', 'Request blocked' );
			};
			add_filter( 'pre_http_request', $block_http_request );
			set_current_screen( 'post.php' );
			remove_filter( 'pre_http_request', $block_http_request );
			add_filter( 'replace_editor', '__return_false' );
			add_filter( 'use_block_editor_for_post', '__return_true' );
		}

		if ( $post_type_supports_amp ) {
			register_post_type( $post_type, [ 'public' => true ] );
			$GLOBALS['post'] = self::factory()->post->create( [ 'post_type' => $post_type ] );

			$previous_user = wp_get_current_user();
			wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

			$supported_post_types = array_merge(
				AMP_Options_Manager::get_option( Option::SUPPORTED_POST_TYPES ),
				[ $post_type ]
			);
			AMP_Options_Manager::update_option( Option::SUPPORTED_POST_TYPES, $supported_post_types );

			wp_set_current_user( $previous_user instanceof WP_User ? $previous_user->ID : $previous_user );
		}
	}
}
