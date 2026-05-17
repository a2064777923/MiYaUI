<?php
/**
 * Plugin Name: MiyaUI Auth Bridge
 * Description: Minimal private REST bridge for MiyaUI Phase 2 password verification.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function miyaui_auth_bridge_secret() {
	if ( defined( 'MIYAUI_BRIDGE_SECRET' ) && MIYAUI_BRIDGE_SECRET ) {
		return MIYAUI_BRIDGE_SECRET;
	}

	return getenv( 'WORDPRESS_AUTH_BRIDGE_SECRET' ) ?: '';
}

function miyaui_auth_bridge_response( WP_User $user ) {
	$all_caps = array_filter(
		(array) $user->allcaps,
		static function ( $enabled ) {
			return (bool) $enabled;
		}
	);

	return array(
		'authenticated' => true,
		'user'          => array(
			'wordpress_user_id' => (int) $user->ID,
			'login'             => (string) $user->user_login,
			'email'             => (string) $user->user_email,
			'display_name'      => (string) $user->display_name,
			'nicename'          => (string) $user->user_nicename,
			'roles'             => array_values( (array) $user->roles ),
			'capabilities'      => $all_caps,
		),
	);
}

add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'miyaui/v1',
			'/auth/verify',
			array(
				'methods'             => 'POST',
				'permission_callback' => static function ( WP_REST_Request $request ) {
					$secret = miyaui_auth_bridge_secret();
					if ( ! $secret ) {
						return new WP_Error( 'bridge_secret_missing', 'Bridge secret is not configured.', array( 'status' => 500 ) );
					}

					$header_secret = (string) $request->get_header( 'x-miyaui-bridge-secret' );
					if ( ! hash_equals( $secret, $header_secret ) ) {
						return new WP_Error( 'bridge_forbidden', 'Forbidden.', array( 'status' => 403 ) );
					}

					return true;
				},
				'callback'            => static function ( WP_REST_Request $request ) {
					$params            = (array) $request->get_json_params();
					$username_or_email = isset( $params['username_or_email'] ) ? sanitize_text_field( wp_unslash( $params['username_or_email'] ) ) : '';
					$password          = isset( $params['password'] ) ? (string) $params['password'] : '';

					if ( '' === $username_or_email || '' === $password ) {
						return new WP_REST_Response(
							array(
								'authenticated' => false,
								'code'          => 'invalid_request',
							),
							400
						);
					}

					$user = is_email( $username_or_email )
						? get_user_by( 'email', $username_or_email )
						: get_user_by( 'login', $username_or_email );

					if ( ! $user instanceof WP_User ) {
						return new WP_REST_Response(
							array(
								'authenticated' => false,
								'code'          => 'user_not_found',
							),
							401
						);
					}

					$is_valid = wp_check_password( $password, $user->user_pass, $user->ID );
					if ( ! $is_valid ) {
						return new WP_REST_Response(
							array(
								'authenticated' => false,
								'code'          => 'invalid_credentials',
							),
							401
						);
					}

					return new WP_REST_Response( miyaui_auth_bridge_response( $user ), 200 );
				},
			)
		);
	}
);
