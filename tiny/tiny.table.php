<?php
class TinyTable {
	private $name = '';
	private $fields = array();
	private $keys = array();
	public function __construct( $name, $fields, $keys ) {
		global $wpdb;
		$this->name 	= "{$wpdb->prefix}{$name}";
		$this->fields = $fields;
		$this->keys 	= $keys;
		$this->create();
	}
	public function create() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;
		global $charset_collate;
		$fields = implode( ','.PHP_EOL, $this->fields );
		$keys = array();
		foreach( $this->keys as $k => $value ) {
			if ( 0 === $k ) {
				$keys[] = "PRIMARY KEY  ({$value})";
			} else {
				$keys[] = "KEY {$value} ({$value})";
			}
		}
		$keys = implode( ','.PHP_EOL, $keys );
		$sql = "CREATE TABLE {$this->name} (".PHP_EOL."{$fields}, {$keys}".PHP_EOL.") {$charset_collate};";
		dbDelta( $sql );
	}
	public function get( $args=array() ) {
		global $wpdb;
		// $args = wp_parse_args( $args, $defaults );
		$fields = implode( ', ', $args['fields'] );
		$where = array();
		$where_values = array();
		if ( isset( $args['where'] ) ) {
			foreach ( $args['where'] as $where_key => $where_args ) {
				$where_defaults = array( 'comparison' => '=' );
				$where_args = wp_parse_args( $where_args, $where_defaults );
				$where[ $where_key ] = " `{$where_args['column']}` {$where_args['comparison']} ";
				$value = $where_args['value'];
				if ( is_array( $value ) ) {
					$where[ $where_key ] .= '%s AND %s';
					$where_values[] = $value[0];
					$where_values[] = $value[1];
				} else {
					if ( is_numeric( $value ) && is_int( $value ) ) {
						$where[ $where_key ] .= '%d';
					} else if ( is_numeric( $value ) && is_float( $value ) ) {
						$where[ $where_key ] .= '%f';
					} else {
						$where[ $where_key ] .= '%s';
					}
					$where_values[] = $value;
				}
			}
		}
		$sql = "SELECT {$fields} FROM `{$this->name}`";
		if ( $where ) {
			$sql .= ' WHERE ('.implode( ') AND (', $where ).')';
			$sql = $wpdb->prepare( $sql, $where_values );
		}
		if ( isset( $args['groupby'] ) ) {
			$sql .= ' GROUP BY '.implode( ',', $args['groupby'] );
		}
		if ( isset( $args['orderby'] ) ) {
			$sql .= ' ORDER BY '.implode( ',', $args['orderby'] );
		}
		$result = $wpdb->get_results( $sql, ARRAY_A );
		return $result;
	}
	public function delete( $data ) {
		global $wpdb;
		return $wpdb->delete( $this->name, $data );
	}
	public function add( $data ) {
		global $wpdb;
		$wpdb->insert( $this->name, $data );
		return $wpdb->insert_id;
		// $fields = array_keys( $data );
		// $fields = '`'.implode( '`, `', $fields ).'`';
		// $placeholders = array();
		// foreach( $data as $value ) {
		// 	if ( is_numeric( $value ) && is_int( $value ) ) {
		// 		$placeholders[] = '%d';
		// 	} else if ( is_numeric( $value ) && is_float( $value ) ) {
		// 		$placeholders[] = '%f';
		// 	} else {
		// 		$placeholders[] = '%s';
		// 	}
		// }
		// $placeholders = implode( ', ', $placeholders );
		// $sql = "INSERT INTO `{$this->name}` ({$fields}) VALUES({$placeholders})";
		// $sql = $wpdb->prepare( $sql, $data );
		// $wpdb->
		// echo $sql;
		// wp_die();
	}
}
