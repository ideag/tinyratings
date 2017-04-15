<?php
/**
 * Stars ratings style.
 *
 * @package TinyRatingsStars
 */

/**
 * Stars ratings style class.
 */
class TinyRatingsStars {
	/**
	 * Inintialize ratings style.
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'tinyratings_settings', 					array( 'TinyRatingsStars', 'settings' ) );
		add_filter( 'tinyratings_buttons_stars', 			array( 'TinyRatingsStars', 'buttons' ) );
		add_filter( 'tinyratings_result_stars', 			array( 'TinyRatingsStars', 'result' ), 10, 3 );
		add_filter( 'tinyratings_top_atts_stars', 		array( 'TinyRatingsStars', 'top_atts' ), 10, 3 );
		add_filter( 'tinyratings_duplicate_stars',		array( 'TinyRatingsStars', 'duplicate' ), 10, 4 );
		add_filter( 'tinyratings_content_stars',			array( 'TinyRatingsStars', 'schema' ), 10, 2 );
	}
	/**
	 * Filter in style-specific settings
	 *
	 * @param  array $settings Plugin Settings.
	 * @return array 					Plugin Settings.
	 */
	public static function settings( $settings ) {
		$settings['sections']['defaults']['fields']['style']['list']['stars'] = do_shortcode( '[tinyrating id="0" type="demo" style="stars" inline="true"]' );
		$settings['sections']['defaults']['fields']['structured_data'] = array(
			'title' => __( 'Structured Data', 'tinyratings' ),
			'callback'	=> 'checkbox',
			'label'			=> __( 'Generate structured data for Google rich snippets.', 'tinyratings' ),
		);
		return $settings;
	}
	/**
	 * Filter in style-specific buttons
	 *
	 * @param  array $buttons Buttons.
	 * @return array 					Buttons.
	 */
	public static function buttons( $buttons ) {
		$buttons = array(
			array(
				'value' => 1,
				'icon'  => '<span class="dashicons dashicons-star-empty"></span>',
				'title' => __( '1 star', 'tinyratings' ),
			),
			array(
				'value' => 2,
				'icon'  => '<span class="dashicons dashicons-star-empty"></span>',
				'title' => __( '2 stars', 'tinyratings' ),
			),
			array(
				'value' => 3,
				'icon'  => '<span class="dashicons dashicons-star-empty"></span>',
				'title' => __( '3 stars', 'tinyratings' ),
			),
			array(
				'value' => 4,
				'icon'  => '<span class="dashicons dashicons-star-empty"></span>',
				'title' => __( '4 stars', 'tinyratings' ),
			),
			array(
				'value' => 5,
				'icon'  => '<span class="dashicons dashicons-star-empty"></span>',
				'title' => __( '5 stars', 'tinyratings' ),
			),
		);
		return $buttons;
	}
	/**
	 * Modify result returned via API.
	 *
	 * @param  mixed  $result      Results.
	 * @param  int    $object_id   Rated object ID.
	 * @param  string $object_type Rated object type.
	 * @return mixed							 Results.
	 */
	public static function result( $result, $object_id, $object_type = 'post' ) {
		$sum = 0;
		$count = 0;
		if ( ! is_array( $result ) ) {
			$result = array();
		}
		foreach ( $result as $key => $row ) {
			$sum += $row['rating_value'] * $row['count'];
			$count += $row['count'];
		}
		if ( 0 < $count ) {
			$result = round( $sum / $count, 2 );
		} else {
			$result = 0;
		}
		return $result;
	}
	/**
	 * Modify how top results are decided
	 *
	 * @param  array  $search         Search attributes.
	 * @param  string $object_type    Rated object type.
	 * @param  string $object_subtype Rated object subtype.
	 * @return mixed							    Results.
	 */
	public static function top_atts( $search, $object_type, $object_subtype ) {
		$search['fields']['count'] = 'SUM(`rating_value`) / COUNT( * )  AS `count`';
		return $search;
	}
	/**
	 * How to handle repeated rating requests
	 *
	 * @param  array $result Result API object.
	 * @param  bool  $change Should rating chagnes be allowed.
	 * @param  array $data   Further matches from ratings.
	 * @param  array $args   Full array of new rating request.
	 * @return array 					Result API object.
	 */
	public static function duplicate( $result, $change, $data, $args ) {
		if ( $change ) {
			TinyRatings::$ratings->delete( $result['current'] );
			if ( $args['rating_value'] !== $result['current']['rating_value'] ) {
				$args['rating_id'] = TinyRatings::$ratings->add( $args );
				$data = array( $args );
			}
			$result['current'] = array_pop( $data );
		}
		return $result;
	}
	public static function schema( $return, $atts ) {
		$defaults = array(
			'force_schema' => false,
		);
		$atts = wp_parse_args( $atts, $defaults );
		if ( !TinyRatings::$options['structured_data'] ) {
			return $return;
		}
		if ( ! is_main_query() && ! $atts['force_schema'] ) {
			return $return;
		}
		$obj = get_queried_object();
		if (
			! ( 'tax' === $atts['type'] && isset( $obj->taxonomy ) )
			&&
			! ( 'post' === $atts['type'] && is_singular() )
			&&
			! $atts['force_schema']
		) {
			return $return;
		}
		$result = TinyRatings::get_result( $atts['id'], $atts['style'], $atts['type'] );
		if ( 0 === $result ) {
			return $return;
		}
		$raw_result = TinyRatings::get_result( $atts['id'], $atts['style'], $atts['type'], true );
		$count = 0;
		foreach ( $raw_result as $item ) {
			$count += $item['count'];
		}
		$return .= '<script type="application/ld+json">';
		$schema_data = array(
			'@context' 	=> 'http://schema.org/',
			'@type'				=> 'aggregateRating',
			'itemReviewed' => array(
				'@type'	=> 'Thing',
			),
			'ratingValue' => $result,
			'bestRating'	=> 5,
			'ratingCount' => $count,
		);
		if ( 'tax' === $atts['type'] ) {
			$schema_data['itemReviewed']['name'] = $obj->name;
			$schema_data['itemReviewed']['description'] = $obj->description;
			$schema_data['itemReviewed']['url'] = get_term_link( $obj );
		}
		if ( 'post' === $atts['type'] ) {
			$schema_data['itemReviewed']['name'] = get_the_title();
			$schema_data['itemReviewed']['description'] = get_the_excerpt();
			$schema_data['itemReviewed']['url'] = get_the_permalink();
			$img = get_the_post_thumbnail_url( get_the_id(), 'medium' );
			if ( $img ) {
				$schema_data['itemReviewed']['image'] = $img;
			}
		}
		$return .= wp_json_encode( $schema_data );
		$return .= '</script>';
		return $return;
	}
}
