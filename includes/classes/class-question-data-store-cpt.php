<?php
/**
 * Question data store class.
 *
 * Copyright (C) 2015 WooCommerce
 * Copied from WooCommerce WC_Order class and modified a little.
 * Original file: https://github.com/woocommerce/woocommerce/blob/master/includes/class-wc-order.php
 *
 * @package AnsPress
 * @subpackage Classes
 * @since 4.2.0
 */

namespace AnsPress;
defined( 'ABSPATH' ) || exit;

/**
 * Question Data Store: Stored in CPT.
 *
 * @version 4.2.0
 */
class Question_Data_Store_CPT extends Data_Store_WP {

	/**
	 * Internal meta type used to store question data.
	 *
	 * @var string
	 */
	protected $meta_type = 'post';

	/**
	 * Data stored in meta keys, but not considered "meta" for an question.
	 *
	 * @var array
	 */
	protected $internal_meta_keys = array(

	);

	/**
	 * Method to create a new question in the database.
	 *
	 * @param Question $question Question object.
	 */
	public function create( &$question ) {
		$question->set_date_created( current_time( 'timestamp', true ) );
		$question->set_version( AP_VERSION );

		if ( empty( $question->get_title() ) ) {
			throw new \Exception( __( 'Question cannot be created without a title.', 'anspress-question-answer' ) );
		}

		/**
		 * Question data can be filtered before creating new question.
		 *
		 * @param array $data Question data.
		 * @since 4.2.0
		 */
		$data = apply_filters( 'ap_new_question_data',
			array(
				'post_title'    => $question->get_title( 'edit' ),
				'post_content'  => $question->get_content( 'edit' ),
				'post_date'     => gmdate( 'Y-m-d H:i:s', $question->get_date_created( 'edit' )->getOffsetTimestamp() ),
				'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $question->get_date_created( 'edit' )->getTimestamp() ),
				'post_type'     => $question->get_type( 'edit' ),
				'post_status'   => ( $question->get_status( 'edit' ) ? $question->get_status( 'edit' ) : apply_filters( 'woocommerce_default_order_status', 'pending' ) ),
				'ping_status'   => 'closed',
				'post_author'   => 1,
				//'post_password' => uniqid( 'order_' ),
				'post_parent'   => $question->get_parent_id( 'edit' ),
				'post_excerpt'  => '',
			)
		);

		$id = wp_insert_post( $data, true );

		if ( $id && ! is_wp_error( $id ) ) {
			$question->set_id( $id );
			$this->update_post_meta( $question );
			$question->save_meta_data();
			$question->apply_changes();
			$this->clear_caches( $question );
		}

		/**
		 * After new question created.
		 *
		 * @param integer Question id.
		 * @since 4.2.0
		 */
		do_action( 'ap_new_question', $question->get_id() );
	}

	/**
	 * Method to read a question from the database.
	 *
	 * @param Question $question Question object.
	 * @throws \Exception If passed question is invalid.
	 */
	public function read( &$question ) {
		$question->set_defaults();
		$post_object = get_post( $question->get_id() );

		if ( ! $question->get_id() || ! $post_object || ! in_array( $post_object->post_type, [ 'question' ], true ) ) {
			throw new \Exception( __( 'Invalid question.', 'anspress-question-answer' ) );
		}

		$question->set_props( array(
			'title'         => $post_object->post_title,
			'content'       => $post_object->post_content,
			'author_id'     => $post_object->post_author,
			'parent_id'     => $post_object->post_parent,
			'date_created'  => $post_object->post_date_gmt,
			'date_created'  => 0 < $post_object->post_date_gmt ? string_to_timestamp( $post_object->post_date_gmt ) : null,
			'date_modified' => $post_object->post_modified_gmt,
			'date_modified' => 0 < $post_object->post_modified_gmt ? string_to_timestamp( $post_object->post_modified_gmt ) : null,
			'status'        => $post_object->post_status,
		) );

		$this->read_question_data( $question, $post_object );
		$question->read_meta_data();
		$question->set_object_read( true );
	}

	/**
	 * Read question data.
	 *
	 * @param Question $question    Question object.
	 * @param object   $post_object Post object.
	 */
	protected function read_question_data( &$question, $post_object ) {
		$id = $question->get_id();

		// Set default meta keys.
		foreach ( $question->get_meta_props() as $meta_key => $map ) {
			$function = 'set_' . $map;
			if ( is_callable( array( $question, $function ) ) ) {
				$question->{$function}( get_post_meta( $question->get_id(), $meta_key, true ) );
			}
		}

		// Gets extra data associated with the question if needed.
		foreach ( $question->get_extra_data_keys() as $key ) {
			$function = 'set_' . $key;
			if ( is_callable( array( $question, $function ) ) ) {
				$question->{$function}( get_post_meta( $question->get_id(), '_' . $key, true ) );
			}
		}
	}

	/**
	 * Method to update an question in the database.
	 *
	 * @param Question $question Question object.
	 */
	public function update( &$question ) {
		$question->save_meta_data();
		$question->set_version( AP_VERSION );

		if ( null === $question->get_date_created( 'edit' ) ) {
			$question->set_date_created( current_time( 'timestamp', true ) );
		}

		$changes = $question->get_changes();

		$valid_fields = [ 'date_created', 'date_modified', 'status', 'parent_id', 'post_excerpt', 'title', 'content' ];


		// Throw error on empty title.
		if ( empty( $question->get_title() ) ) {
			throw new \Exception( __( 'Question title is empty.', 'anspress-question-answer' ) );
		}

		// Only update the post when the post data changes.
		if ( array_intersect( $valid_fields, array_keys( $changes ) ) ) {
			$post_data = array(
				'post_title'        => $question->get_title( 'edit' ),
				'post_content'      => $question->get_content( 'edit' ),
				'post_author'       => $question->get_author_id( 'edit' ),
				'post_date'         => gmdate( 'Y-m-d H:i:s', $question->get_date_created( 'edit' )->getOffsetTimestamp() ),
				'post_date_gmt'     => gmdate( 'Y-m-d H:i:s', $question->get_date_created( 'edit' )->getTimestamp() ),
				'post_status'       => ( $question->get_status( 'edit' ) ? $question->get_status( 'edit' ) : apply_filters( 'ap_default_question_status', 'pending' ) ),
				'post_parent'       => $question->get_parent_id( 'edit' ),
				'post_excerpt'      => $this->get_post_excerpt( $question ),
				'post_modified'     => isset( $changes['date_modified'] ) ? gmdate( 'Y-m-d H:i:s', $question->get_date_modified( 'edit' )->getOffsetTimestamp() ) : current_time( 'mysql' ),
				'post_modified_gmt' => isset( $changes['date_modified'] ) ? gmdate( 'Y-m-d H:i:s', $question->get_date_modified( 'edit' )->getTimestamp() ) : current_time( 'mysql', 1 ),
			);

			/**
			 * When updating this object, to prevent infinite loops, use $wpdb
			 * to update data, since wp_update_post spawns more calls to the
			 * save_post action.
			 *
			 * This ensures hooks are fired by either WP itself (admin screen save),
			 * or an update purely from CRUD.
			 */
			if ( doing_action( 'save_post' ) ) {
				$GLOBALS['wpdb']->update( $GLOBALS['wpdb']->posts, $post_data, array( 'ID' => $order->get_id() ) );
				clean_post_cache( $question->get_id() );
			} else {
				wp_update_post( array_merge( array( 'ID' => $question->get_id() ), $post_data ) );
			}

			$question->read_meta_data( true ); // Refresh internal meta data, in case things were hooked into `save_post` or another WP hook.
		}

		$this->update_post_meta( $question );
		$question->apply_changes();
		$this->clear_caches( $question );

		/**
		 * Action triggered after updating a question.
		 *
		 * @param integer $id Question id.
		 * @since 4.2.0
		 */
		do_action( 'ap_update_question', $question->get_id() );
	}

	/**
	 * Method to delete an answer from the database.
	 *
	 * @param Question $question Question object.
	 * @param array    $args     Array of args to pass to the delete method.
	 *
	 * @return void
	 */
	public function delete( &$question, $args = array() ) {
		$id   = $question->get_id();
		$args = wp_parse_args( $args, [ 'force_delete' => false ] );

		if ( ! $id ) {
			return;
		}

		if ( $args['force_delete'] ) {
			wp_delete_post( $id );
			$question->set_id( 0 );
			do_action( 'ap_delete_question', $id );
		} else {
			wp_trash_post( $id );
			$question->set_status( 'trash' );
			do_action( 'ap_trash_question', $id );
		}
	}

	/**
	 * Helper method that updates all the post meta for an question based on it's settings in the Question class.
	 *
	 * @param Question $question Question object.
	 */
	protected function update_post_meta( &$question ) {
		$updated_props   = array();
		$id              = $question->get_id();
		$props_to_update = $this->get_props_to_update( $question, $question->get_meta_props() );

		foreach ( $props_to_update as $meta_key => $prop ) {
			$value = $question->{"get_$prop"}( 'edit' );
			// If date object get timestamp.
			if ( $value instanceof \AnsPress\DateTime ) {
				$value = $value->date( 'Y-m-d H:i:s' );
			}

			if ( update_post_meta( $question->get_id(), $meta_key, $value ) ) {
				$updated_props[] = $prop;
			}
		}

		/**
		 * Action after updating question object properties/meta.
		 *
		 * @param Question $question      Question object.
		 * @param array    $updated_props Updated properties.
		 * @since 4.2.0
		 */
		do_action( 'ap_question_object_updated_props', $question, $updated_props );
	}

	/**
	 * Clear any caches.
	 *
	 * @param Question $question Question object.
	 */
	protected function clear_caches( &$question ) {
		clean_post_cache( $question->get_id() );
		//wc_delete_shop_order_transients( $order );
		wp_cache_delete( 'question-items-' . $question->get_id(), 'questions' );
	}

	/**
	 * Read question items of a specific type from the database for this question.
	 *
	 * @param  Question $question Question object.
	 * @param  string   $type     Question item type.
	 * @return array
	 * @todo Improve this
	 */
	public function read_items( $question, $type ) {
		global $wpdb;

		// Get from cache if available.
		$items = 0 < $question->get_id() ? wp_cache_get( 'question-items-' . $question->get_id(), 'questions' ) : false;

		if ( false === $items ) {
			$items = $wpdb->get_results(
				$wpdb->prepare( "SELECT order_item_type, order_item_id, order_id, order_item_name FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = %d ORDER BY order_item_id;", $order->get_id() )
			);
			foreach ( $items as $item ) {
				wp_cache_set( 'item-' . $item->order_item_id, $item, 'order-items' );
			}
			if ( 0 < $question->get_id() ) {
				wp_cache_set( 'order-items-' . $question->get_id(), $items, 'orders' );
			}
		}

		$items = wp_list_filter( $items, array( 'order_item_type' => $type ) );

		if ( ! empty( $items ) ) {
			//$items = array_map( array( 'WC_Order_Factory', 'get_order_item' ), array_combine( wp_list_pluck( $items, 'order_item_id' ), $items ) );
		} else {
			$items = array();
		}

		return $items;
	}

	/**
	 * Remove all line items (products, coupons, shipping, taxes) from the question.
	 *
	 * @param Question $question Question object.
	 * @param string   $type     Question item type. Default null.
	 * @todo Improve this
	 */
	public function delete_items( $question, $type = null ) {
		global $wpdb;
		if ( ! empty( $type ) ) {
			$wpdb->query( $wpdb->prepare( "DELETE FROM itemmeta USING {$wpdb->prefix}woocommerce_order_itemmeta itemmeta INNER JOIN {$wpdb->prefix}woocommerce_order_items items WHERE itemmeta.order_item_id = items.order_item_id AND items.order_id = %d AND items.order_item_type = %s", $order->get_id(), $type ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = %d AND order_item_type = %s", $order->get_id(), $type ) );
		} else {
			$wpdb->query( $wpdb->prepare( "DELETE FROM itemmeta USING {$wpdb->prefix}woocommerce_order_itemmeta itemmeta INNER JOIN {$wpdb->prefix}woocommerce_order_items items WHERE itemmeta.order_item_id = items.order_item_id and items.order_id = %d", $order->get_id() ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = %d", $order->get_id() ) );
		}
		$this->clear_caches( $question );
	}

	/**
	 * Generate meta query for wc_get_orders.
	 *
	 * @param  array  $values List of customers ids or emails.
	 * @param  string $relation 'or' or 'and' relation used to build the WP meta_query.
	 * @return array
	 * @todo improve
	 */
	private function get_orders_generate_customer_meta_query( $values, $relation = 'or' ) {
		$meta_query = array(
			'relation'        => strtoupper( $relation ),
			'customer_emails' => array(
				'key'     => '_billing_email',
				'value'   => array(),
				'compare' => 'IN',
			),
			'customer_ids'    => array(
				'key'     => '_customer_user',
				'value'   => array(),
				'compare' => 'IN',
			),
		);
		foreach ( $values as $value ) {
			if ( is_array( $value ) ) {
				$query_part = $this->get_orders_generate_customer_meta_query( $value, 'and' );
				if ( is_wp_error( $query_part ) ) {
					return $query_part;
				}
				$meta_query[] = $query_part;
			} elseif ( is_email( $value ) ) {
				$meta_query['customer_emails']['value'][] = sanitize_email( $value );
			} elseif ( is_numeric( $value ) ) {
				$meta_query['customer_ids']['value'][] = strval( absint( $value ) );
			} else {
				return new WP_Error( 'woocommerce_query_invalid', __( 'Invalid customer query.', 'woocommerce' ), $values );
			}
		}

		if ( empty( $meta_query['customer_emails']['value'] ) ) {
			unset( $meta_query['customer_emails'] );
			unset( $meta_query['relation'] );
		}

		if ( empty( $meta_query['customer_ids']['value'] ) ) {
			unset( $meta_query['customer_ids'] );
			unset( $meta_query['relation'] );
		}

		return $meta_query;
	}

	/**
	 * Get valid WP_Query args from a WC_Order_Query's query variables.
	 *
	 * @since 3.1.0
	 * @param array $query_vars query vars from a WC_Order_Query.
	 * @return array
	 */
	protected function get_wp_query_args( $query_vars ) {

		// Map query vars to ones that get_wp_query_args or WP_Query recognize.
		$key_mapping = array(
			'customer_id'    => 'customer_user',
			'status'         => 'post_status',
			'currency'       => 'order_currency',
			'version'        => 'order_version',
			'discount_total' => 'cart_discount',
			'discount_tax'   => 'cart_discount_tax',
			'shipping_total' => 'order_shipping',
			'shipping_tax'   => 'order_shipping_tax',
			'cart_tax'       => 'order_tax',
			'total'          => 'order_total',
			'page'           => 'paged',
		);

		foreach ( $key_mapping as $query_key => $db_key ) {
			if ( isset( $query_vars[ $query_key ] ) ) {
				$query_vars[ $db_key ] = $query_vars[ $query_key ];
				unset( $query_vars[ $query_key ] );
			}
		}

		$wp_query_args = parent::get_wp_query_args( $query_vars );

		if ( ! isset( $wp_query_args['date_query'] ) ) {
			$wp_query_args['date_query'] = array();
		}
		if ( ! isset( $wp_query_args['meta_query'] ) ) {
			$wp_query_args['meta_query'] = array();
		}

		$date_queries = array(
			'date_created'   => 'post_date',
			'date_modified'  => 'post_modified',
			'date_completed' => '_date_completed',
			'date_paid'      => '_date_paid',
		);
		foreach ( $date_queries as $query_var_key => $db_key ) {
			if ( isset( $query_vars[ $query_var_key ] ) && '' !== $query_vars[ $query_var_key ] ) {

				// Remove any existing meta queries for the same keys to prevent conflicts.
				$existing_queries = wp_list_pluck( $wp_query_args['meta_query'], 'key', true );
				$meta_query_index = array_search( $db_key, $existing_queries, true );
				if ( false !== $meta_query_index ) {
					unset( $wp_query_args['meta_query'][ $meta_query_index ] );
				}

				$wp_query_args = $this->parse_date_for_wp_query( $query_vars[ $query_var_key ], $db_key, $wp_query_args );
			}
		}

		if ( isset( $query_vars['customer'] ) && '' !== $query_vars['customer'] && array() !== $query_vars['customer'] ) {
			$values         = is_array( $query_vars['customer'] ) ? $query_vars['customer'] : array( $query_vars['customer'] );
			$customer_query = $this->get_orders_generate_customer_meta_query( $values );
			if ( is_wp_error( $customer_query ) ) {
				$wp_query_args['errors'][] = $customer_query;
			} else {
				$wp_query_args['meta_query'][] = $customer_query;
			}
		}

		if ( isset( $query_vars['anonymized'] ) ) {
			if ( $query_vars['anonymized'] ) {
				$wp_query_args['meta_query'][] = array(
					'key'   => '_anonymized',
					'value' => 'yes',
				);
			} else {
				$wp_query_args['meta_query'][] = array(
					'key'     => '_anonymized',
					'compare' => 'NOT EXISTS',
				);
			}
		}

		if ( ! isset( $query_vars['paginate'] ) || ! $query_vars['paginate'] ) {
			$wp_query_args['no_found_rows'] = true;
		}

		return apply_filters( 'woocommerce_order_data_store_cpt_get_orders_query', $wp_query_args, $query_vars, $this );
	}

	/**
	 * Query for Orders matching specific criteria.
	 *
	 * @since 3.1.0
	 *
	 * @param array $query_vars query vars from a WC_Order_Query.
	 *
	 * @return array|object
	 */
	public function query( $query_vars ) {
		$args = $this->get_wp_query_args( $query_vars );

		if ( ! empty( $args['errors'] ) ) {
			$query = (object) array(
				'posts'         => array(),
				'found_posts'   => 0,
				'max_num_pages' => 0,
			);
		} else {
			$query = new WP_Query( $args );
		}

		$orders = ( isset( $query_vars['return'] ) && 'ids' === $query_vars['return'] ) ? $query->posts : array_filter( array_map( 'wc_get_order', $query->posts ) );

		if ( isset( $query_vars['paginate'] ) && $query_vars['paginate'] ) {
			return (object) array(
				'orders'        => $orders,
				'total'         => $query->found_posts,
				'max_num_pages' => $query->max_num_pages,
			);
		}

		return $orders;
	}
}
