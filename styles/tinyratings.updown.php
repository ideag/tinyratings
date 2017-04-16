<?php
/**
 * Like/Dislike ratings style.
 *
 * @package TinyRatingsUpDown
 */

/**
 * Like/Dislike ratings style class.
 */
class TinyRatingsUpDown {
	/**
	 * Inintialize ratings style.
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'tinyratings_settings', 					array( 'TinyRatingsUpDown', 'settings' ) );
		add_filter( 'tinyratings_buttons_updown', 		array( 'TinyRatingsUpDown', 'buttons' ) );
		add_filter( 'tinyratings_result_updown', 			array( 'TinyRatingsUpDown', 'result' ), 10, 3 );
		add_filter( 'tinyratings_top_atts_updown', 		array( 'TinyRatingsUpDown', 'top_atts' ), 10, 3 );
		add_filter( 'tinyratings_duplicate_updown', 	array( 'TinyRatingsUpDown', 'duplicate' ), 10, 4 );
	}
	/**
	 * Filter in style-specific settings
	 *
	 * @param  array $settings Plugin Settings.
	 * @return array 					Plugin Settings.
	 */
	public static function settings( $settings ) {
		$settings['sections']['defaults']['fields']['style']['list']['updown'] = do_shortcode( '[tinyrating id="0" type="demo" style="updown" inline="true"]' );
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
				'icon'  => '<span class="dashicons dashicons-arrow-up-alt"></span>',
				'title' => __( 'Like', 'tinyratings' ),
			),
			array(
				'value' => -1,
				'icon'  => '<span class="dashicons dashicons-arrow-down-alt"></span>',
				'title' => __( 'Dislike', 'tinyratings' ),
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
			$sum 		+= $row['rating_value'] * $row['count'];
			$count 	+= $row['count'];
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
