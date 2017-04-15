<?php
/**
 * Manin plugin class
 *
 * @package TinyRatings
 */

// [ ] TO DO: widgets.
// initialize plugin.
// add_action( 'plugins_loaded', array( 'TinyRatings', 'init' ) );

/**
 * Main plugin class.
 */
class TinyRatings {
	/**
	 * Plugin Settings default values.
	 *
	 * @var array
	 */
	public static $options = array(
		'style' 						=> 'stars', 			// options: like | likedislike | stars.
		'permissions'  			=> 'any',					// options: guests | users | any.
		'log'					 			=> array( 'rating_fingerprint' ),
		'allow_change' 			=> true,
		'structured_data'		=> true,
		'append_posttype'		=> array( 'post' ),
		'append_position'		=> 'after',				// options: before | after | none.
	);
	/**
	 * Plugin Settings object.
	 *
	 * @var array
	 */
	public static $settings = false;
	/**
	 * Plugin path.
	 *
	 * @var object
	 */
	public static $plugin_path = '';
	/**
	 * Plugin path.
	 *
	 * @var object
	 */
	private static $_id_counter = array();
	/**
	 * Built-in styles
	 *
	 * @var object
	 */
	public static $styles = array(
		 'like'					=> 'TinyRatingsLike',
		 'likedislike'	=> 'TinyRatingsLikeDislike',
		 'updown'				=> 'TinyRatingsUpDown',
		 'stars'				=> 'TinyRatingsStars',
	 );
	/**
	 * Ratings Table
	 *
	 * @var object
	 */
	public static $ratings = false;
	/**
	 * Plugin initialization
	 *
	 * @return void
	 */
	public static function init() {
		self::$plugin_path = plugin_dir_path( __FILE__ );

		// tinyOptions v 0.5.0.
		self::$options = wp_parse_args( get_option( 'tinyratings_options' ), self::$options );
		add_action( 'plugins_loaded', array( 'TinyRatings', 'init_options' ), 9999 - 0050 );
		add_action( 'init', array( 'TinyRatings', 'init_settings' ), 101 );

		// TinyTable v 0.1.0.
		add_action( 'plugins_loaded', array( 'TinyRatings', 'init_tables' ), 9999 - 0010 );
		add_action( 'plugins_loaded', array( 'TinyRatings', 'create_tables' ), 9999 );

		add_action( 'wp_enqueue_scripts', 		array( 'TinyRatings', 'scripts' ) );
		add_action( 'wp_enqueue_scripts', 		array( 'TinyRatings', 'styles' ) );
		add_action( 'admin_enqueue_scripts', 	array( 'TinyRatings', 'scripts' ) );
		add_action( 'admin_enqueue_scripts', 	array( 'TinyRatings', 'styles' ) );

		add_filter( 'tinyratings_compare_fields', array( 'TinyRatings', 'log' ) );

		add_shortcode( 'tinyrating',  		array( 'TinyRatings', 'shortcode' ) );
		add_shortcode( 'taxrating',   		array( 'TinyRatings', 'shortcode_tax' ) );
		add_shortcode( 'listrating',  		array( 'TinyRatings', 'shortcode_list' ) );
		add_shortcode( 'tinyrating_top',  array( 'TinyRatings', 'shortcode_top' ) );

		add_action( 'rest_api_init', array( 'TinyRatings', 'api' ) );

		add_action( 'init', array( 'TinyRatings', 'init_styles' ), 100 );

		add_filter( 'the_content', array( 'TinyRatings', 'append' ) );
	}
	/**
	 * Auto-insert ratings to post content
	 *
	 * @param  string $content post content.
	 * @return string
	 */
	public static function append( $content ) {
		$post_id = get_the_id();
		$post_type = get_post_type( $post_id );
		if ( ! in_array( $post_type, self::$options['append_posttype'], true ) ) {
			return $content;
		}
		switch ( self::$options['append_position'] ) {
			case 'before' :
				$content = "[tinyrating]\r\n\r\n{$content}";
			break;
			case 'after' :
				$content = "{$content}\r\n\r\n[tinyrating	]";
			break;
		}
		return $content;
	}
	/**
	 * Load built-in ratings styles
	 *
	 * @return void
	 */
	public static function init_styles() {
		foreach ( self::$styles as $style => $class ) {
			require_once( self::$plugin_path . "styles/tinyratings.{$style}.php" );
		}
		self::$styles = apply_filters( 'tinyratings_styles', self::$styles );
		foreach ( self::$styles as $style => $class ) {
			$class::init();
		}
	}

	/**
	 * Register REST API route
	 *
	 * @return void
	 */
	public static function api() {
		register_rest_route(
			'tinyratings/v1',
			'/(?P<type>[^/]+)/(?P<id>\d+)',
			array(
				array(
					'methods'  => WP_REST_Server::READABLE,
					'callback' => array( 'TinyRatings', 'api_read' ),
				),
				array(
					'methods'  => WP_REST_Server::CREATABLE,
					'callback' => array( 'TinyRatings', 'api_create' ),
					'permission_callback'	=> array( 'TinyRatings', 'api_create_permission' ),
				),
			)
		);
	}
	/**
	 * Return current rating results via REST API
	 *
	 * @param  object $request WP_REST_Request object.
	 * @return object          WP_REST_Response object.
	 */
	public static function api_read( $request ) {
		$args = array(
			'rating_fingerprint'	=> $request['fingerprint'],
			'rating_timestamp'		=> current_time( 'mysql' ),
			'rating_style'				=> $request['style'],
			'rating_ip'						=> self::_get_ip(),
			'object_id' 					=> $request['id'],
			'object_type'					=> $request['type'],
		);
		if ( is_user_logged_in() ) {
			$args['user_id'] = get_current_user_id();
		}
		$data = self::_get_current_rating( $args );
		$result = array(
			'current' => array_pop( $data ),
		);
		$result['result']	= self::get_result( $args['object_id'], $args['rating_style'], $args['object_type'] );
		return rest_ensure_response( $result );
	}
	/**
	 * Add a new rating via REST API
	 *
	 * @param  object $request WP_REST_Request object.
	 * @return object          WP_REST_Response object.
	 */
	public static function api_create( $request ) {
		$args = array(
			'rating_fingerprint'	=> $request['fingerprint'],
			'rating_timestamp'		=> current_time( 'mysql' ),
			'rating_value'				=> $request['rating'],
			'rating_style'				=> $request['style'],
			'rating_ip'						=> self::_get_ip(),
			'object_id' 					=> $request['id'],
			'object_type'					=> $request['type'],
			'object_subtype'			=> $request['subtype'],
		);
		if ( is_user_logged_in() ) {
			$args['user_id'] = get_current_user_id();
		}
		$data = self::_get_current_rating( $args );
		$result = array(
			'current' => array_pop( $data ),
		);
		if ( 0 < count( $result['current'] ) ) {
			if ( ! self::$options['allow_change'] ) {
				$result['error'] = new WP_Error(
					'tinyratings_duplicate',
					esc_html__( 'Your rating is already in the database!', 'tinyratings' ),
					array(
						'status' => 406,
					)
				);
			}
			$result = apply_filters( "tinyratings_duplicate_{$args['rating_style']}", $result, self::$options['allow_change'], $data, $args );
			$result = apply_filters( 'tinyratings_duplicate', $result, self::$options['allow_change'], $data, $args );
		} else {
			$args['rating_id'] = self::$ratings->add( $args );
			$result['current'] = $args;
		}
		delete_transient( "tr_result_{$args['object_id']}_{$args['object_type']}_{$args['rating_style']}" );
		delete_transient( "tr_top_result_{$args['object_type']}_{$args['rating_style']}" );
		$result['result']	= self::get_result( $args['object_id'], $args['rating_style'], $args['object_type'] );
		return rest_ensure_response( $result );
	}
	/**
	 * Filter for identification factors
	 *
	 * @param  array $compare Identification factors.
	 * @return array          Identification factors.
	 */
	public static function log( $compare ) {
		$compare = $compare + self::$options['log'];
		return $compare;
	}
	/**
	 * Get rating for the current visitor, if one is available
	 *
	 * @param  array $args Ratings information.
	 * @return array 			 Ratings data.
	 */
	private static function _get_current_rating( $args ) {
		$search = array(
			'fields' => array( '*' ),
			'where' => array(),
		);
		$compare = array( 'object_id', 'object_type', 'object_subtype', 'rating_style' );
		$compare = apply_filters( "tinyratings_compare_fields_{$rating_style}", $compare );
		$compare = apply_filters( 'tinyratings_compare_fields', $compare );
		foreach ( $args as $key => $value ) {
			if ( ! in_array( $key, $compare, true ) ) {
				continue;
			}
			$search['where'][ $key ] = array(
				'column'	=> $key,
				'value'		=> $value,
			);
		}
		$data = self::$ratings->get( $search );
		return $data;
	}
	/**
	 * Get IP address of the current visitor.
	 *
	 * @return string IP address or false if not detected.
	 */
	private static function _get_ip() {
		// @codingStandardsIgnoreStart
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			// Check ip from share internet.
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			// Check ip is pass from proxy.
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		// @codingStandardsIgnoreEnd
		// Ignoring CodingStandards here because they are throwing false positives. The IP is properly sanitized and validated before usage.
		$ip = wp_unslash( $ip );
		$ip = rest_is_ip_address( $ip );
		return apply_filters( 'tinyratings_get_ip', $ip );
	}
	/**
	 * Manage REST API access to POST verb
	 *
	 * @param  object $request WP_REST_Request object.
	 * @return object          WP_REST_Response object.
	 */
	public static function api_create_permission( $request ) {
		if ( is_user_logged_in() && in_array( self::$options['permissions'], array( 'any', 'users' ), true ) ) {
			return true;
		}
		if ( ! is_user_logged_in() && in_array( self::$options['permissions'], array( 'any', 'guests' ), true ) ) {
			return true;
		}
		return false;
	}
	/**
	 * Enqueue plugin styles.
	 *
	 * @return void
	 */
	public static function styles() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		wp_register_style( 'tinyratings', plugins_url( 'tinyratings.css', __FILE__ ) );
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style( 'tinyratings' );
	}
	/**
	 * Enqueue plugin scripts.
	 *
	 * @return void
	 */
	public static function scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		wp_register_script( 'fingerprintjs2', plugins_url( "js/fingerprintjs2/dist/fingerprint2{$suffix}.js", __FILE__ ) );
		wp_register_script( 'tinyratings', plugins_url( 'tinyratings.js', __FILE__ ), array( 'jquery', 'fingerprintjs2' ), false, true );
		wp_enqueue_script( 'tinyratings' );
		$data = array(
			'api_uri' 	=> get_rest_url( null, 'tinyratings/v1' ),
			'api_nonce' => wp_create_nonce( 'wp_rest' ),
		);
		wp_localize_script( 'tinyratings', 'tinyratings_data', $data );
	}
	/**
	 * Main shortcode callback.
	 *
	 * @param  array  $atts    Shortcode attributes.
	 * @param  string $content Shortcode content.
	 * @return string       	 Shortcode output.
	 */
	public static function shortcode( $atts = array(), $content = '' ) {
		$defaults = array(
			'id'			=> get_the_id(),
			'type'		=> 'post',
			'subtype'	=> get_post_field( 'post_type' ),
			'style'		=> self::$options['style'],
			'inline'	=> false,
			'float'		=> false,
			'active'	=> true,
		);
		$atts = wp_parse_args( $atts, $defaults );
		$return  = '';
		$container_class = array( 'tinyratings-container', "tinyratings-style-{$atts['style']}" );
		if ( $atts['inline'] ) {
			$container_class[] = 'tinyratings-inline';
		}
		if ( $atts['float'] ) {
			$container_class[] = "tinyratings-float-{$atts['float']}";
		}
		if ( $atts['active'] ) {
			$container_class[] = 'tinyratings-active';
		}
		$container_class = implode( ' ', $container_class );
		$return .= '<div class="tinyratings-vote">';
		$buttons = array();
		$buttons = apply_filters( "tinyratings_buttons_{$atts['style']}", $buttons );
		$buttons = apply_filters( 'tinyratings_buttons', $buttons );
		foreach ( $buttons as $button ) {
			$button_defaults = array(
				'title' => false,
			);
			$button = wp_parse_args( $button, $button_defaults );
			$class = array( 'tinyratings-button' );
			$class = implode( ' ', $class );
			$return .= ' <span class="' . $class . '" data-rating="' . $button['value'] . '" title="' . $button['title'] . '">' . $button['icon'] . '</span>';
		}
		$return .= '</div>';
		$return .= '<div class="tinyratings-result">';
		$return .= self::get_result( $atts['id'], $atts['style'], $atts['type'] );
		$return .= '</div>';
		$return = apply_filters( "tinyratings_content_{$atts['style']}", $return, $atts );
		$return = apply_filters( 'tinyratings_content', $return, $atts );
		$return = '<div class="' . $container_class . '" data-style="' . $atts['style'] . '" data-object-id="' . $atts['id'] . '" data-object-type="' . $atts['type'] . '" data-object-subtype="' . $atts['subtype'] . '">' . $return . '</div>';
		return $return;
	}
	/**
	 * Taxonomy shortcode callback.
	 *
	 * @param  array  $atts    Shortcode attributes.
	 * @param  string $content Shortcode content.
	 * @return string       	 Shortcode output.
	 */
	public static function shortcode_tax( $atts = array(), $content = '' ) {
		$defaults = array(
			'type'		=> 'term',
			'subtype'	=> 'category',
		);
		$tax = get_queried_object();
		if ( isset( $tax->taxonomy ) ) {
			$defaults['id'] = $tax->term_id;
			$defaults['subtype'] = $tax->taxonomy;
		}
		$atts = wp_parse_args( $atts, $defaults );
		if ( ! isset( $atts['id'] ) ) {
			return false;
		}
		if ( ! isset( $atts['subtype'] ) ) {
			$tax = get_term( $atts['id'] );
			$atts['subtype'] = $tax->taxonomy;
		}
		$result = self::shortcode( $atts, $content );
		return $result;
	}
	/**
	 * Taxonomy shortcode callback.
	 *
	 * @param  array  $atts    Shortcode attributes.
	 * @param  string $content Shortcode content.
	 * @return string       	 Shortcode output.
	 */
	public static function shortcode_list( $atts = array(), $content = '' ) {
		$defaults = array(
			'type'		=> 'list',
			'subtype' => get_the_id(),
			'style'		=> self::$options['style'],
		);
		$atts = wp_parse_args( $atts, $defaults );
		if ( ! isset( $atts['id'] ) ) {
			if ( ! isset( self::$_id_counter[ "{$atts['subtype']}-{$atts['style']}" ] ) ) {
				self::$_id_counter[ "{$atts['subtype']}-{$atts['style']}" ] = -1;
			}
			++self::$_id_counter[ "{$atts['subtype']}-{$atts['style']}" ];
			$atts['id'] = self::$_id_counter[ "{$atts['subtype']}-{$atts['style']}" ];
		}
		$result = self::shortcode( $atts, $content );
		return $result;
	}
	/**
	 * Main shortcode callback.
	 *
	 * @param  array  $atts    Shortcode attributes.
	 * @param  string $content Shortcode content.
	 * @return string       	 Shortcode output.
	 */
	public static function shortcode_top( $atts = array(), $content = '' ) {
		$defaults = array(
			'type'		=> 'post',
			'subtype'	=> false,
			'style'		=> self::$options['style'],
			'rating'	=> true,
			'active'	=> false,
			'float'		=> false,
			'limit'   => 5,
		);
		$atts = wp_parse_args( $atts, $defaults );
		$return  = '';
		$results = self::get_top( $atts['style'], $atts['type'], $atts['subtype'], $atts['limit'] );
		$container_class = array( 'tinyratings-top-container', "tinyratings-top-style-{$atts['style']}" );
		$container_class = implode( ' ', $container_class );
		$return .= '<ol>';
		foreach ( $results as $result ) {
			$ratings = '';
			if ( $atts['rating'] ) {
				$ratings .= do_shortcode( " [tinyrating type='{$atts['type']}' subtype='{$atts['subtype']}' style='{$atts['style']}' id='{$result['object_id']}' inline='true' active='{$atts['active']}' active='{$atts['float']}']" );
			}
			switch ( $atts['type'] ) {
				case 'post' :
					$href = get_permalink( $result['object_id'] );
					$title = get_the_title( $result['object_id'] );
				break;
				case 'term' :
				case 'tax' :
				case 'taxonomy' :
					$term = get_term( $result['object_id'] );
					if ( is_wp_error( $term ) ) {
						continue;
					}
					$href = get_term_link( $term );
					$title = $term->name;
				break;
			}
			$return .= "<li><a href='{$href}'>{$title}</a>{$ratings}</li>";
		}
		$return .= '</ol>';
		$return = apply_filters( "tinyratings_top_content_{$atts['style']}", $return, $atts );
		$return = apply_filters( 'tinyratings__top_content', $return, $atts );
		$return = '<div class="' . $container_class . '">' . $return . '</div>';
		return $return;
	}

	/**
	 * Get top objects
	 *
	 * @param  string  $rating_style   Rating style.
	 * @param  string  $object_type    Object type, by default - post.
	 * @param  boolean $object_subtype Object subtype.by default - any.
	 * @param  integer $limit          How many objects to show.
	 * @return array                   –;ist of object.
	 */
	public static function get_top( $rating_style, $object_type = 'post', $object_subtype = false, $limit = 5 ) {
		$result = get_transient( "tr_top_result_{$object_type}_{$rating_style}" );
		if ( ! $result ) {
			$result = array();
			$search = array(
				'fields' => array(
					'object_id',
					'count' => 'COUNT(*) as `count`',
				),
				'where' => array(
					'object_type' => array(
						'column'	=> 'object_type',
						'value'		=> $object_type,
					),
					'rating_style' => array(
						'column'	=> 'rating_style',
						'value'		=> $rating_style,
					),
				),
				'groupby' => array( 'object_id' ),
				'orderby' => array( '`count` DESC' ),
				'limit'		=> $limit,
			);
			if ( $object_subtype ) {
				$search['where']['object_subtype'] = array(
					'column'	=> 'object_subtype',
					'value'		=> $object_subtype,
				);
			}
			$search = apply_filters( "tinyratings_top_atts_{$rating_style}", $search, $object_type, $object_subtype );
			$search = apply_filters( 'tinyratings_top_atts', $search, $object_type, $object_subtype, $rating_style );
			$result = self::$ratings->get( $search );
			set_transient( "tr_top_result_{$object_type}_{$rating_style}", $result );
		}
		return $result;
	}

	/**
	 * Get full rating results.
	 *
	 * @param  int    $object_id    Rated object ID.
	 * @param  string $rating_style Ratings style.
	 * @param  string $object_type  Rated object type.
	 * @param  bool   $raw  				Should result be parsed for display.
	 * @return array 								Rating results.
	 */
	public static function get_result( $object_id, $rating_style, $object_type = 'post', $raw = false ) {
		$result = get_transient( "tr_result_{$object_id}_{$object_type}_{$rating_style}" );
		if ( ! $result ) {
			$result = array();
			$search = array(
				'fields' => array(
					'rating_value',
					'COUNT(*) as `count`',
				),
				'where' => array(
					'object_type' => array(
						'column'	=> 'object_type',
						'value'		=> $object_type,
					),
					'object_id' => array(
						'column'	=> 'object_id',
						'value'		=> $object_id,
					),
					'rating_style' => array(
						'column'	=> 'rating_style',
						'value'		=> $rating_style,
					),
				),
				'groupby' => array( 'rating_value' ),
			);
			$search = apply_filters( "tinyratings_result_atts_{$rating_style}", $search, $object_id, $object_type );
			$search = apply_filters( 'tinyratings_result_atts', $search, $object_id, $object_type, $rating_style );
			$result = self::$ratings->get( $search );
			set_transient( "tr_result_{$object_id}_{$object_type}_{$rating_style}", $result );
		}
		if ( ! $raw ) {
			$result = apply_filters( "tinyratings_result_{$rating_style}", $result, $object_id, $object_type );
			$result = apply_filters( 'tinyratings_result', $result, $object_id, $object_type, $rating_style );
		}
		return $result;
	}

	/**
	 * Create ratings table
	 *
	 * @return void
	 */
	public static function create_tables() {
		self::$ratings = new TinyTable(
			'tinyratings',
			array(
				'rating_id bigint(20) unsigned NOT NULL AUTO_INCREMENT',
				'rating_timestamp datetime NOT NULL default NOW()',
				'rating_value int NOT NULL default "0"',
				'rating_ip varchar(40) NOT NULL default ""',
				'rating_style varchar(40) NOT NULL default ""',
				'rating_fingerprint varchar(100) NOT NULL default ""',
				'object_id bigint(20) unsigned NOT NULL default 0',
				'object_type varchar(20) NOT NULL default "post"',
				'object_subtype varchar(20) NOT NULL default "post"',
				'user_id bigint(20) unsigned NOT NULL default "0"',
			),
			array(
				'rating_id'
			)
		);
	}

	/**
	 * Plugin Settings initialization
	 *
	 * @return void
	 */
	public static function init_options() {
		self::$settings = array(
			'page' => array(
				'title' 			=> __( 'tinyRatings Settings', 'tinyratings' ),
				'menu_title'	=> __( 'tinyRatings', 'tinyratings' ),
				'slug' 				=> 'tinyratings-settings',
				'option'			=> 'tinyratings_options',
				'description'	=> __( 'This plugin allows you to simply add ratings to pretty much any piece of content in WordPress. Default usage is to add <code>[tinyrating]</code> shortcode anywhere in post content.', 'tinyratings' ),
			),
			'sections' => array(
				'defaults' => array(
					'title'				=> __( 'Main Settings	', 'tinyratings' ),
					'fields'	=> array(
						'style' => array(
							'title'	=> __( 'Rating Style', 'tinyratings' ),
							'callback' => 'listfield',
							'attributes' => array(
								'type'	=> 'radio',
							),
							'list' => array(),
						),
						'permissions' => array(
							'title'	=> __( 'Who Can Rate?', 'tinyratings' ),
							'callback' => 'listfield',
							'list' => array(
								'guests'	=> __( 'Guests', 					'tinyratings' ),
								'users'		=> __( 'Logged-in Users',	'tinyratings' ),
								'any'			=> __( 'Everybody', 			'tinyratings' ),
							),
						),
						'log' => array(
							'title'	=> __( 'Log Using', 'tinyratings' ),
							'callback' => 'listfield',
							'attributes' => array(
								'type'	=> 'checkbox',
							),
							'list' => array(
								'rating_fingerprint'	=> __( 'Browser Fingerprint', 					'tinyratings' ),
								'rating_ip'					  => __( 'IP Address', 					'tinyratings' ),
								'user_id'							=> __( 'User ID', 					'tinyratings' ),
								'rating_timetamp'		  => __( 'No Logging',	'tinyratings' ),
							),
						),
						'allow_change' => array(
							'title' => __( 'Allow Changes', 'tinyratings' ),
							'callback'	=> 'checkbox',
							'label'			=> __( 'Allow visitors to change their rating once they submitted it', 'tinyratings' ),
						),
						'local_fingerprint' => array(
							'title' => __( 'Local Fingerprint', 'tinyratings' ),
							'callback'	=> 'checkbox',
							'label'			=> __( 'Load FingerprintJS2 from local copy.', 'tinyratings' ),
						),
					),
				),
				'append' => array(
					'title'				=> __( 'Append Settings	', 'tinyratings' ),
					'description'	=> __( 'tinyRatings can display ratinngs automatically at the begining or the end of the post content. Here you can setup which post types shoud display ratings and where.', 'tinyratings' ),
					'fields'	=> array(
						'append_posttype' => array(
							'title'	=> __( 'Post Types', 'tinyratings' ),
							'description'	=> __( 'Which post types should display ratings automatically?', 'tinyratings' ),
							'callback' => 'listfield',
							'attributes' => array(
								'type'	=> 'checkbox',
							),
							'list' => '_get_post_types',
						),
						'append_position' => array(
							'title'				=> __( 'Location', 'tinyratings' ),
							'description'	=> __( 'Where should ratings be inserted?', 'tinyratings' ),
							'callback' 		=> 'listfield',
							'list' => array(
								'before'	=> __( 'Before Post Content', 	'tinyratings' ),
								'after'		=> __( 'After Post Content', 		'tinyratings' ),
								'none'		=> __( '- none -', 							'tinyratings' ),
							),
						),
					),
				),
			),
			'l10n' => array(
				'no_access'			=> __( 'You do not have sufficient permissions to access this page.', 'tinyratings' ),
				'save_changes'	=> esc_attr( 'Save Changes', 'tinyratings' ),
			),
		);
		require_once( self::$plugin_path . 'tiny/tiny.options.php' );
	}
	/**
	 * Intialize plugin settings.
	 *
	 * @return void
	 */
	public static function init_settings() {
		self::$settings = apply_filters( 'tinyratings_settings', self::$settings );
		self::$settings = new tinyOptions( self::$settings, __CLASS__ );
	}
	/**
	 * Intialize plugin table library.
	 *
	 * @return void
	 */
	public static function init_tables() {
		require_once( self::$plugin_path . 'tiny/tiny.table.php' );
	}
}
