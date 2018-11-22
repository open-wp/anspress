<?php
/**
 * Question and answer query filters.
 *
 * @package AnsPress
 * @since 4.0.0
 */

// Bail if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Query hooks
 */
class AP_QA_Query_Hooks {

	/**
	 * Alter WP_Query mysql query for question and answers.
	 *
	 * @param  array  $sql      Sql query.
	 * @param  Object $wp_query Instance.
	 * @return array
	 * @since unknown
	 * @since 4.1.7  Fixed: Session answers are included in wrong question.
	 * @since 4.1.8  Fixed: Sorting issue with best answer.
	 * @since 4.1.13 Do not include session posts to question query.
	 * @since 4.2.0  Cleaned up mysql query.
	 */
	public static function sql_filter( $sql, $wp_query ) {
		global $wpdb;

		// Do not filter if wp-admin.
		if ( is_admin() ) {
			return $sql;
		}

		if ( isset( $wp_query->query['ap_query'] ) ) {
			$sql['join']   = $sql['join'] . " LEFT JOIN {$wpdb->ap_qameta} qameta ON qameta.post_id = {$wpdb->posts}.ID";
			$sql['fields'] = $sql['fields'] . ', qameta.*, qameta.votes_up - qameta.votes_down AS votes_net';
			$post_status   = '';
			$query_status  = $wp_query->query['post_status'];

			// Hack to fix WP_Query for fetching anonymous author posts.
			if ( isset( $wp_query->query['author'] ) && 0 === $wp_query->query['author'] ) {
				$sql['where'] = $sql['where'] . $wpdb->prepare( " AND {$wpdb->posts}.post_author = %d", $wp_query->query['author'] );
			}

			$query_sorting = isset( $wp_query->query['ap_order_by'] ) ? $wp_query->query['ap_order_by'] : 'active';
			$query_filter  = isset( $wp_query->query['ap_filter_by'] ) ? $wp_query->query['ap_filter_by'] : 'all';
			$answer_query  = isset( $wp_query->query['ap_answers_query'] );

			if ( ! empty( $query_sorting ) ) {
				$sorting  = $answer_query ? AnsPress\get_answers_sorting() : AnsPress\get_questions_sorting();
				$order_by = isset( $sorting[ $query_sorting ] ) ? $sorting[ $query_sorting ]['sql'] : $sorting['active']['sql'];

				$sql['orderby'] = sprintf( $order_by, $wpdb->posts, $wpdb->ap_qameta );
			}

			if ( ! empty( $query_filter ) ) {
				$filter    = AnsPress\get_questions_filter();
				$filter_by = isset( $filter[ $query_filter ] ) ? $filter[ $query_filter ]['sql'] : '';

				if ( 'all' !== $query_filter && ! empty( $filter_by ) ) {
					$sql['where'] = $sql['where'] . ' AND(' . sprintf( $filter_by, $wpdb->posts, $wpdb->ap_qameta ) . ')';
				}
			}

			// Keep featured posts on top.
			if ( ! $answer_query ) {
				$sql['orderby'] = 'CASE WHEN IFNULL(qameta.featured, 0) =1 THEN 1 ELSE 2 END ASC, ' . $sql['orderby'];
			}

			// Keep best answer to top.
			if ( $answer_query && ! $wp_query->query['ignore_selected_answer'] ) {
				$sql['orderby'] = 'case when qameta.selected = 1 then 1 else 2 end, ' . $sql['orderby'];
			}

			// Allow filtering sql query.
			$sql = apply_filters( 'ap_qa_sql', $sql );

			$wp_query->count_request = $sql;
		}

		return $sql;
	}

	/**
	 * Add qameta fields to post and prefetch metas and users.
	 *
	 * @param  array  $posts Post array.
	 * @param  object $instance QP_Query instance.
	 * @return array
	 * @since 3.0.0
	 * @since 4.1.0 Fixed: qameta fields are not appending properly.
	 */
	public static function posts_results( $posts, $instance ) {
		global $question_rendered;

		foreach ( (array) $posts as $k => $p ) {
			if ( in_array( $p->post_type, [ 'question', 'answer' ], true ) ) {
				// Convert object as array to prevent using __isset of WP_Post.
				$p_arr = (array) $p;

				// Check if ptype exists which is a qameta feild.
				if ( ! empty( $p_arr['ptype'] ) ) {
					$qameta = ap_qameta_fields();
				} else {
					$qameta = ap_get_qameta( $p->ID );
				}

				foreach ( (array) $qameta as $fields_name => $val ) {
					if ( ! isset( $p_arr[ $fields_name ] ) || empty( $p_arr[ $fields_name ] ) ) {
						$p->$fields_name = $val;
					}
				}

				// Serialize fields and activities.
				$p->activities = maybe_unserialize( $p->activities );
				$p->fields     = maybe_unserialize( $p->fields );

				$p->ap_qameta_wrapped = true;
				$p->votes_net         = $p->votes_up - $p->votes_down;

				// Replace content if user cannot read.
				if ( ! ap_user_can_read_post( $p, false, $p->post_type ) ) {
					$p->post_content = __( 'Restricted content', 'anspress-question-answer' );
				} else {
					$posts[ $k ] = $p;
				}
			}
		} // End foreach().

		if ( isset( $instance->query['ap_question_query'] ) || isset( $instance->query['ap_answers_query'] ) ) {
			$instance->pre_fetch();
		}

		return $posts;
	}
}
