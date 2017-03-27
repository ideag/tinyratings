<?php
/**
 * Like ratings style.
 *
 * @package TinyRatingsLike
 */

/**
 * Like ratings style class.
 */
class TinyRatingsLike {
	/**
	 * Inintialize ratings style.
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'tinyratings_settings', array( 'TinyRatingsLike', 'settings' ) );
		add_filter( 'tinyratings_buttons_like', array( 'TinyRatingsLike', 'buttons' ) );
		add_filter( 'tinyratings_result_atts_like', array( 'TinyRatingsLike', 'result_atts' ), 10, 3 );
		add_filter( 'tinyratings_result_like', array( 'TinyRatingsLike', 'result' ), 10, 3 );
		add_filter( 'tinyratings_duplicate_like', array( 'TinyRatingsLike', 'duplicate' ), 10, 4 );
	}
	/**
	 * Filter in style-specific settings
	 *
	 * @param  array $settings Plugin Settings.
	 * @return array 					Plugin Settings.
	 */
	public static function settings( $settings ) {
		$settings['sections']['defaults']['fields']['style']['list']['like'] = do_shortcode( '[tinyrating id="0" type="demo" style="like" inline="true"]' );
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
		);
		return $buttons;
	}
	/**
	 * Modify result request attributes.
	 *
	 * @param  array  $atts        Result request attributes.
	 * @param  int    $object_id   Rated object ID.
	 * @param  string $object_type Rated object type.
	 * @return array 							 Result request attributes.
	 */
	public static function result_atts( $atts, $object_id, $object_type = 'post' ) {
		$atts['fields'] = array( 'COUNT( * ) as `count`' );
		unset( $atts['groupby'] );
		return $atts;
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
		foreach ( $result as $key => $row ) {
			$sum += $row['count'];
		}
		return $sum;
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
			$result['current'] = array_pop( $data );
		}
		return $result;
	}
}
