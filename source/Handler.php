<?php

namespace HPosts;

use HPosts\Contracts\StaticInitiator;
use HPosts\Utilities\Constants;

use WP_Screen, WP_Post_Type, WP_Post;

class Handler {

	use StaticInitiator;

	public $settings = [];

	public function __construct() {
		$this->settings = [
			'permalinks' => Constants::get( 'PERMALINKS', true ),
			'post_types' => Constants::get( 'TYPES', [] )
		];

		/**
		 * @link https://developer.wordpress.org/reference/hooks/registered_post_type/
		 */
		add_action( 'registered_post_type', [ $this, '__enableHierarchyFields' ], 20, 2 );

		/**
		 * @link https://developer.wordpress.org/reference/hooks/post_type_labels_post_type/
		 */
		foreach ( $this->getSetting( 'post_types', [] ) as $post_type ) {
			add_filter( 'post_type_labels_' . $post_type, [ $this, '__enableHierarchyFieldsForJs' ], 20, 2 );
		}

		/**
		 * If we have permalink rewrites setting enabled.
		 */
		if ( $this->getSetting( 'permalinks' ) ) {

			/**
			 * @todo This only applies to the `post` post type. Look for global way to do this.
			 * @link https://developer.wordpress.org/reference/hooks/pre_post_link/
			 */
			add_filter( 'pre_post_link', [ $this, '__modifyPermalinks' ], 8, 3 );

			/**
			 * @link https://developer.wordpress.org/reference/hooks/init/
			 */
			add_action( 'init', [ $this, '__modifyRewriteRules' ], 25 );

			/**
			 * @link https://developer.wordpress.org/reference/hooks/current_screen/
			 */
			add_action( 'current_screen', [ $this, '__checkPrettyUrls' ] );
		}

	}

	/**
	 * @param string $name
	 * @param null $default
	 *
	 * @return mixed|null
	 */
	public function getSetting( string $name, $default = null ) {
		if ( array_key_exists( $name, $this->settings ) ) {
			return $this->settings[ $name ];
		}

		return $default;
	}

	/**
	 * @param WP_Post $post
	 *
	 * @return string
	 */
	public function buildPostParentUri( $post ) {
		$final = '';

		if ( !empty( $post->post_parent ) ){
			$post_parent = get_post( $post->post_parent );

			while ( !empty( $post_parent ) ) {
				$final = $post_parent->post_name . '/' . $final;

				if ( !empty( $post_parent->post_parent ) ) {
					$post_parent = get_post( $post_parent->post_parent);
				} else {
					break;
				}
			}
		}

		return $final;
	}

	/**
	 * @param string $post_type
	 * @param WP_Post_Type $post_type_object
	 */
	public function __enableHierarchyFields( string $post_type, $post_type_object ) {
		$post_types = $this->getSetting( 'post_types', [] );
		if ( in_array( $post_type, $post_types ) ) {
			$post_type_object->hierarchical = true;

			add_post_type_support( $post_type, 'page-attributes' );
		}
	}

	/**
	 * @param object $labels
	 */
	public function __enableHierarchyFieldsForJs( $labels ) {
		$labels->parent_item_colon = sprintf( __( 'Parent %s', 'hposts' ), $labels->name );
		return $labels;
	}

	/**
	 * @param string $permalink
	 * @param WP_Post $post
	 * @param bool $leavename
	 *
	 * @return string
	 */
	public function __modifyPermalinks( string $permalink, $post, bool $leavename ) {
		$post_types = $this->getSetting( 'post_types', [] );

		if ( in_array( $post->post_type, $post_types ) && strpos( $permalink, '%postname%' ) !== false ) {
			$prefix = trim( $this->buildPostParentUri( $post ), '/' );
			if ( $prefix ) {
				$permalink = str_replace( '%postname%', $prefix . '/%postname%', $permalink );
			}
		}

		return $permalink;
	}

	/**
	 *
	 */
	public function __modifyRewriteRules() {

		/**
		 * Override the default postname regex
		 */
		add_rewrite_tag( '%postname%', '(?:.+/)?([^/]+)', 'name=' );

	}

	/**
	 * @param WP_Screen $current_screen
	 */
	public function __checkPrettyUrls( $current_screen ) {
		$post_types = $this->getSetting( 'post_types', [] );

		if (
			$current_screen->base === 'post' &&
			!empty( $current_screen->post_type ) &&
			in_array( $current_screen->post_type, $post_types ) &&
			!get_option( 'permalink_structure' )
		) {
			/**
			 * @todo Add notice.
			 * You have chosen to have hierarchied permalinks for your {post_type}, but first you need to set correct permalinks in `Settings > Permalinks` otherwise it won't work!
			 */
		}
	}
}
