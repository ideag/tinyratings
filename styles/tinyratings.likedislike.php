<?php
/**
 * Like/Dislike ratings style.
 *
 * @package TinyRatingsLikeDislike
 */

/**
 * Like/Dislike ratings style class.
 */
class TinyRatingsLikeDislike {
	/**
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'tinyratings_settings', 								array( 'TinyRatingsLikeDislike', 'settings' ) );
		add_filter( 'tinyratings_buttons_likedislike', 			array( 'TinyRatingsLikeDislike', 'buttons' ) );
		add_filter( 'tinyratings_result_likedislike', 			array( 'TinyRatingsLikeDislike', 'result' ), 10, 3 );
		add_filter( 'tinyratings_top_atts_likedislike', 		array( 'TinyRatingsLikeDislike', 'top_atts' ), 10, 3 );
		add_filter( 'tinyratings_duplicate_likedislike', 		array( 'TinyRatingsLikeDislike', 'duplicate' ), 10, 4 );
	}
	/**
	 * Filter in style-specific settings
	 *
	 * @param  array $settings Plugin Settings.
	 * @return array 					Plugin Settings.
	 */
	public static function settings( $settings ) {
		$settings['sections']['defaults']['fields']['style']['list']['likedislike'] = do_shortcode( '[tinyrating id="0" type="demo" style="likedislike" inline="true"]' );
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
				'icon'  => '<span class="dashicons dashicons-thumbs-up"></span>',
				'title' => __( 'Like', 'tinyratings' ),
			),
			array(
				'value' => -1,
				'icon'  => '<span class="dashicons dashicons-thumbs-down"></span>',
				'title' => __( 'DIslike', 'tinyratings' ),
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
		return TinyRatings::format_result( $sum, $count );
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
		$search['fields']['count'] = 'SUM(`rating_value`) AS `count`';
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
}
