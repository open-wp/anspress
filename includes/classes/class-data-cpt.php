<?php
/**
 * Common methods between question and answer cpt.
 *
 * @package AnsPress
 * @subpackage Classes
 * @since 4.2.0
 */

namespace AnsPress;
defined( 'ABSPATH' ) || exit;

/**
 * CPT data class.
 *
 * @since 4.2.0
 */
class Data_Cpt extends Abstracts\Data {
	/**
	 * Stores data about status changes so relevant hooks can be fired.
	 *
	 * @var bool|array
	 */
	protected $status_transition = false;

	/**
	 * All default meta keys.
	 *
	 * @var array
	 */
	protected $meta_props = [
		'_ap_version'                  => 'version',
		'_ap_last_active'              => 'last_active',
		'_ap_last_activity'            => 'last_active',
		'_ap_last_activity_user_id'    => 'last_activity_user_id',
		'_ap_unapproved_comment_count' => 'unapproved_comment_count',
	];

	/**
	 * Set question content.
	 *
	 * @param string $value Value.
	 * @return void
	 */
	public function set_content( $value ) {
		$this->set_prop( 'content', $value );
	}

	/**
	 * Set version of AnsPress.
	 *
	 * @param integer $value Value.
	 * @return void
	 */
	public function set_version( $value ) {
		$this->set_prop( 'version', $value );
	}

	/**
	 * Set question last active date.
	 *
	 * @param string $date Date.
	 * @return void
	 */
	public function set_last_active( $date = null ) {
		$this->set_date_prop( 'last_active', $date );
	}

	/**
	 * Set last activity type.
	 *
	 * @param string $value Activity type.
	 * @return void
	 */
	public function set_last_activity( $value ) {
		$this->set_prop( 'last_activity', sanitize_title( $value ) );
	}

	/**
	 * Set last activity user id.
	 *
	 * @param integer $value Value.
	 * @return void
	 */
	public function set_last_activity_user_id( $value ) {
		$this->set_prop( 'last_activity_user_id', absint( $value ) );
	}

	/**
	 * Set author id.
	 *
	 * @param integer $value Value.
	 * @return void
	 */
	public function set_author_id( $value ) {
		$this->set_prop( 'author_id', absint( $value ) );
	}

	/**
	 * Set date_created.
	 *
	 * @param  string|integer|null $date UTC timestamp, or ISO 8601 DateTime. If the DateTime string has no timezone or offset, WordPress site timezone will be assumed. Null if there is no date.
	 * @throws \Exception Exception may be thrown if value is invalid.
	 */
	public function set_date_created( $date = null ) {
		$this->set_date_prop( 'date_created', $date );
	}

	/**
	 * Set date_modified.
	 *
	 * @param  string|integer|null $date UTC timestamp, or ISO 8601 DateTime. If the DateTime string has no timezone or offset, WordPress site timezone will be assumed. Null if there is no date.
	 * @throws \Exception Exception may be thrown if value is invalid.
	 */
	public function set_date_modified( $date = null ) {
		$this->set_date_prop( 'date_modified', $date );
	}

	/**
	 * Set question up vote counts.
	 *
	 * @param integer $value Value.
	 * @return void
	 */
	public function set_vote_up_counts( $value ) {
		$this->set_prop( 'vote_up_counts', absint( $value ) );
	}

	/**
	 * Set down vote counts.
	 *
	 * @param integer $value Value.
	 * @return void
	 */
	public function set_vote_down_counts( $value ) {
		$this->set_prop( 'vote_down_counts', absint( $value ) );
	}

	/**
	 * Set net vote counts.
	 *
	 * @param integer $value Value.
	 * @return void
	 */
	public function set_vote_net_counts( $value ) {
		$this->set_prop( 'vote_net_counts', absint( $value ) );
	}

	/**
	 * Set comment count.
	 *
	 * @param integer $value Value.
	 * @return void
	 */
	public function set_comment_count( $value ) {
		$this->set_prop( 'comment_count', absint( $value ) );
	}

	/**
	 * Set unapproved comment count.
	 *
	 * @param integer $value Value.
	 * @return void
	 */
	public function set_unapproved_comment_count( $value ) {
		$this->set_prop( 'unapproved_comment_count', absint( $value ) );
	}

	/**
	 * Set question status.
	 *
	 * @param string $new_status    Status to change the question to.
	 * @return array
	 */
	public function set_status( $new_status ) {
		$old_status = $this->get_status();

		// If setting the status, ensure it's set to a valid status.
		if ( true === $this->object_read ) {
			if ( empty( $old_status ) ) {
				$old_status = 'draft';
			}
		}

		$this->set_prop( 'status', $new_status );

		$result = array(
			'from' => $old_status,
			'to'   => $new_status,
		);

		if ( true === $this->object_read && ! empty( $result['from'] ) && $result['from'] !== $result['to'] ) {
			$this->status_transition = array(
				'from'   => ! empty( $this->status_transition['from'] ) ? $this->status_transition['from'] : $result['from'],
				'to'     => $result['to'],
			);
		}

		return $result;
	}

	/**
	 * Handle the status transition.
	 */
	protected function status_transition() {
		$status_transition = $this->status_transition;

		// Reset status transition variable.
		$this->status_transition = false;

		if ( $status_transition ) {
			try {
				$to = $status_transition['to'];

				/**
				 * Action triggered when status of question is updated.
				 *
				 * @param integer            $id       Question id.
				 * @param \AnsPress\Question $instance Question instance.
				 * @since 4.2.0
				 */
				do_action( "ap_question_status_{$to}", $this->get_id(), $this );

				if ( ! empty( $status_transition['from'] ) ) {
					$from = $status_transition['from'];

					/**
					 * Triggered on when question status is changed from one to another.
					 *
					 * @param integer            $id       Question id.
					 * @param \AnsPress\Question $instance Question instance.
					 * @since 4.2.0
					 */
					do_action( "ap_question_status_{$from}_to_{$to}", $this->get_id(), $this );

					/**
					 * Triggered while question status is changed.
					 *
					 * @param string            $from       Previous status.
					 * @param string            $to         New status.
					 * @param \AnsPress\Question $instance Question instance.
					 * @since 4.2.0
					 */
					do_action( 'ap_question_status_changed', $this->get_id(), $from, $to, $this );
				}
			} catch ( Exception $e ) {
				// TODO: Add to logging.
			}
		}
	}

	/**
	 * Updates status of question immediately.
	 *
	 * @param string $new_status Status to change the question to.
	 * @return bool
	 */
	public function update_status( $new_status ) {
		if ( ! $this->get_id() ) { // Question must exist.
			return false;
		}

		try {
			if ( 'trash' !== $new_status ) {
				$this->set_status( $new_status );
				$this->save();
			} elseif ( 'trash' === $this->get_status() && 'trash' !== $new_status ) {
				$this->untrash();
			} else {
				$this->delete();
			}
		} catch ( Exception $e ) {
			// TODO: Add logging.
			return false;
		}

		return true;
	}

	/**
	 * Immediately updates total unapproved comment count.
	 *
	 * @return void
	 */
	public function update_unapproved_comment_count() {
		$comment_count = wp_count_comments( $this->get_id() );
		$this->set_unapproved_comment_count( $comment_count->moderated + $comment_count->spam );
		$this->save();
	}

	/**
	 * Adds comment to the question.
	 *
	 * @param  array $args Comment arguments.
	 * @return int Comment ID.
	 * @todo Change all previous comments type to `ap_cpt_comment`.
	 */
	public function add_comment( $args ) {
		if ( ! $this->get_id() ) {
			return 0;
		}

		$args = wp_array_slice_assoc( $args, [ 'comment_author', 'comment_author_email', 'user_id', 'comment_content' ] );

		$comment_author_email  = strtolower( __( 'anonymous', 'anspress-question-answer' ) ) . '@';
		$comment_author_email .= isset( $_SERVER['HTTP_HOST'] ) ? str_replace( 'www.', '', sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) ) : 'noreply.com'; // WPCS: input var ok.
		$comment_author_email  = sanitize_email( $comment_author_email );

		$args = wp_parse_args( $args, array(
			'comment_author'       => __( 'Anonymous', 'anspress-question-answer' ),
			'comment_author_email' => $comment_author_email,
			'user_id'              => get_current_user_id(),
			'comment_content'      => '',
		) );

		extract( $args );

		$user = get_user_by( 'id', $user_id );

		if ( $user ) {
			$args['comment_author']       = $user->display_name;
			$args['comment_author_email'] = $user->user_email;
			$args['user_id']              = $user->ID;
		}

		$commentdata = apply_filters(
			'ap_new_cpt_comment_data',
			array(
				'comment_post_ID'      => $this->get_id(),
				'comment_author'       => $comment_author,
				'comment_author_email' => $comment_author_email,
				'comment_author_url'   => '',
				'comment_content'      => $comment_content,
				'comment_agent'        => 'AnsPress',
				'comment_type'         => 'ap_cpt_comment',
				'comment_parent'       => 0,
				'user_id'              => $user_id,
			)
		);

		$comment_id = wp_new_comment( $commentdata, true );

		$this->update_unapproved_comment_count();

		$comment_count = wp_count_comments( $this->get_id() );
		$this->set_comment_count( $comment_count->approved );

		return $comment_id;
	}

	/*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get CPT title.
	 *
	 * @param string $context Context.
	 * @return string
	 */
	public function get_title( $context = 'view' ) {
		return $this->get_prop( 'title', $context );
	}

	/**
	 * Get CPT content.
	 *
	 * @param string $context Context.
	 * @return string
	 */
	public function get_content( $context = 'view' ) {
		return $this->get_prop( 'content', $context );
	}

	/**
	 * Get CPT up vote counts.
	 *
	 * @param string $context Context.
	 * @return integer
	 */
	public function get_vote_up_counts( $context = 'view' ) {
		return $this->get_prop( 'vote_up_counts', $context );
	}

	/**
	 * Get CPT down vote counts.
	 *
	 * @param string $context Context.
	 * @return integer
	 */
	public function get_vote_down_counts( $context = 'view' ) {
		return $this->get_prop( 'vote_down_counts', $context );
	}

	/**
	 * Get CPT net vote counts.
	 *
	 * @param string $context Context.
	 * @return integer
	 */
	public function get_vote_net_counts( $context = 'view' ) {
		return $this->get_prop( 'vote_net_counts', $context );
	}

	/**
	 * Get date_created.
	 *
	 * @param  string $context View or edit context.
	 * @return \AnsPress\DateTime|NULL object if the date is set or null if there is no date.
	 */
	public function get_date_created( $context = 'view' ) {
		return $this->get_prop( 'date_created', $context );
	}

	/**
	 * Get date_modified.
	 *
	 * @param  string $context View or edit context.
	 * @return \AnsPress\DateTime|NULL object if the date is set or null if there is no date.
	 */
	public function get_date_modified( $context = 'view' ) {
		return $this->get_prop( 'date_modified', $context );
	}

	/**
	 * Return the question statuses without wc- internal prefix.
	 *
	 * @param  string $context View or edit context.
	 * @return string
	 */
	public function get_status( $context = 'view' ) {
		return $this->get_prop( 'status', $context );
	}

	/**
	 * Output post classes.
	 *
	 * @param string $class Custom CSS classes to append.
	 * @return string
	 * @since 4.2.0
	 */
	public function get_css_classes( $class = '' ) {
		$list = get_post_classes( $class, $this->get_id() );

		if ( is_array( $list ) ) {
			return esc_attr( implode( ' ', $list ) );
		}
	}

	/**
	 * Return label of post status.
	 *
	 * @return string
	 */
	public function get_status_label() {
		$status_obj = get_post_status_object( $this->get_status() );
		return esc_attr( $status_obj->label );
	}

	/**
	 * Get last active date.
	 *
	 * @param string $context Context.
	 * @return boolean
	 */
	public function get_last_active( $context = 'view' ) {
		return $this->get_prop( 'last_active', $context );
	}

	/**
	 * Get CPT version.
	 *
	 * @param string $context Context.
	 * @return string
	 */
	public function get_version( $context = 'view' ) {
		return $this->get_prop( 'version', $context );
	}

	/**
	 * Get last activity on post.
	 *
	 * @param string $context Context.
	 * @return string
	 */
	public function get_last_activity( $context = 'view' ) {
		return $this->get_prop( 'last_activity', $context );
	}

	/**
	 * Get author id.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return int
	 */
	public function get_author_id( $context = 'view' ) {
		return $this->get_prop( 'author_id', $context );
	}

	/**
	 * Get the user associated with the question. False for guests.
	 *
	 * @return WP_User|false
	 */
	public function get_author() {
		return $this->get_author_id() ? get_user_by( 'id', $this->get_author_id() ) : false;
	}


	/**
	 * Get last activity user id.
	 *
	 * @param string $context Context.
	 * @return string
	 */
	public function get_last_activity_user_id( $context = 'view' ) {
		return $this->get_prop( 'last_activity_user_id', $context );
	}

	/**
	 * Get last active time in human readable format.
	 *
	 * @return string
	 */
	public function get_last_active_human_diff() {
		$active_date = $this->get_last_active() ? $this->get_last_active() : $this->get_date_modified();
		return human_time_diff( $active_date->getTimestamp() );
	}

	/**
	 * Get formatted last activity.
	 *
	 * @return string
	 */
	public function get_last_activity_formatted() {
		$activity_type = ap_activity_object()->get_action( $this->get_last_activity() );

		if ( empty( $activity_type ) ) {
			$activity_type = array(
				'verb' => __( 'performed an activity', 'anspress-question-answer' ),
			);
		}

		return sprintf(
			// Translators: %1$s author link, %2$s activity, %3$s time.
			__( '%1$s %2$s %3$s ago', 'anspress-question-answer' ),
			'<a href="' . ap_user_link( $this->get_author_id() ) . '">' . ap_user_display_name( $this->get_author() ) . '</a>',
			$activity_type['verb'],
			$this->get_last_active_human_diff()
		);
	}

	/**
	 * Get comment count.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return int
	 */
	public function get_comment_count( $context = 'view' ) {
		return $this->get_prop( 'comment_count', $context );
	}

	/**
	 * Get unapproved comment count.
	 *
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return int
	 * @todo make sure to recount unapproved comments @migrate.
	 */
	public function get_unapproved_comment_count( $context = 'view' ) {
		return $this->get_prop( 'unapproved_comment_count', $context );
	}

	/**
	 * Output method `get_last_activity_formatted`.
	 *
	 * @return void
	 */
	public function the_last_activity() {
		echo $this->get_last_activity_formatted(); // WPCS: XSS safe.
	}

	/**
	 * List comments.
	 *
	 * @return array
	 */
	public function get_comments( $args = [] ) {
		$args = wp_parse_args( $args, array(
			'display_order' => 'newest',
		) );

		$active_order = ap_sanitize_unslash( 'comments_order', 'r' );
		$active_order = empty( $active_order ) ? 'oldest' : $active_order;

		$comments_args = array(
			'post_id' => $this->get_id(),
			'status'  => 'approve',
			'type'    => '',
			'order'   => 'newest' === $active_order ? 'DESC' : 'ASC',
		);

		// If unapproved order is selcted then show all unapproved comments.
		if ( 'unapproved' === $args['display_order'] ) {
			$comments_args['status'] = 'hold';
		}

		$comments = get_comments( $comments_args );
		return $comments;
	}

	/**
	 * Output date created.
	 *
	 * @param string $context
	 * @return void
	 */
	public function the_date_created( $context = 'view' ) {
		$core_date_format = get_option( 'date_format' );
		$core_time_format = get_option( 'time_format' );

		echo esc_attr( $this->get_date_created( $context )->date_i18n( $core_date_format . ' ' . $core_time_format ) );
	}

	/**
	 * Output the content of post.
	 *
	 * @param string $context
	 * @return void
	 */
	public function the_content( $context = 'view' ) {
		echo apply_filters( 'the_content', $this->get_content() ); // XSS safe.
	}

	/**
	 * Get the avatar of the author.
	 *
	 * @param integer $size Avatar size.
	 * @return string
	 */
	public function get_author_avatar( $size = 40 ) {
		return get_avatar( $this->get_author_id(), $size );
	}

	/**
	 * Check if post is published.
	 *
	 * @return boolean
	 */
	public function is_published() {
		return 'publish' === $this->get_status();
	}

	/**
	 * Check if post is future.
	 *
	 * @return boolean
	 */
	public function is_future() {
		return 'future' === $this->get_status();
	}

	/**
	 * Check if post is private.
	 *
	 * @return boolean
	 */
	public function is_private() {
		return 'private' === $this->get_status();
	}

	/**
	 * Output vote button for question or answer.
	 *
	 * @param integer $post_id Post id.
	 * @return void
	 */
	public function the_votes_button() {
		ap_vote_btn( $this->get_id() );
	}

	/**
	 * Get all frontend options for the post.
	 *
	 * @param array $options Options.
	 * @return array
	 */
	public function get_post_options( $options = [] ) {
		$default_options = array(
			'more' => array(
				'text'        => __( 'More', 'anspress-question-answer' ),
				'sub_options' => [],
				'order'       => 100,
			),
		);

		// Edit post link.
		if ( ap_user_can_edit_post( $this->get_id() ) ) {
			$default_options['edit'] = array(
				'text'  => __( 'Edit', 'anspress-question-answer' ),
				'href'  => ap_post_edit_link( $this->get_id() ),
				'title' => __( 'Edit post', 'anspress-question-answer' ),
			);
		}

		// Make post private.
		if ( ap_user_can_change_status( $this->get_id() ) ) {
			$default_options['toggle_private'] = array(
				'text'  => $this->is_private() ? __( 'Public', 'anspress-question-answer' ) : __( 'Private', 'anspress-question-answer' ),
				'href'  => add_query_arg(
					array(
						'action'    => 'anspress_post_action',
						'ap_action' => 'toggle_private',
						'id'        => $this->get_id(),
						'__nonce'   => wp_create_nonce( 'toggle-private-' . $this->get_id() ),
					),
					admin_url( 'admin-post.php' )
				),
				'title' => $this->is_private() ? __( 'Change visibility to public', 'anspress-question-answer' ) : __( 'Change visibility to private', 'anspress-question-answer' ),
			);
		}

		// Trash post link.
		if ( 'trash' !== $this->get_status() && ap_user_can_delete_post( $this->get_id() ) ) {
			$default_options['trash'] = array(
				'text'  => __( 'Trash', 'anspress-question-answer' ),
				'href'  => add_query_arg(
					array(
						'action'    => 'anspress_post_action',
						'ap_action' => 'trash_post',
						'id'        => $this->get_id(),
						'__nonce'   => wp_create_nonce( 'trash-post-' . $this->get_id() ),
					),
					admin_url( 'admin-post.php' )
				),
				'title' => __( 'Trash this post, admin and moderators can restore it', 'anspress-question-answer' ),
			);
		} elseif ( 'trash' === $this->get_status() && ap_user_can_restore( $this->get_id() ) ) {
			$default_options['restore'] = array(
				'text'  => __( 'Restore', 'anspress-question-answer' ),
				'href'  => add_query_arg(
					array(
						'action'    => 'anspress_post_action',
						'ap_action' => 'restore_post',
						'id'        => $this->get_id(),
						'__nonce'   => wp_create_nonce( 'restore-post-' . $this->get_id() ),
					),
					admin_url( 'admin-post.php' )
				),
				'title' => __( 'Restore post from trash', 'anspress-question-answer' ),
			);
		}

		if ( ap_user_can_change_status_to_moderate() ) {
			$default_options['more']['sub_options']['toggle_unapprove'] = array(
				'text' => 'moderate' === $this->get_status() ? __( 'Approve', 'anspress-question-answer' ) : __( 'Unapprove', 'anspress-question-answer' ),
				'href'  => add_query_arg(
					array(
						'action'    => 'anspress_post_action',
						'ap_action' => 'toggle_unapprove',
						'id'        => $this->get_id(),
						'__nonce'   => wp_create_nonce( 'toggle_unapprove-' . $this->get_id() ),
					),
					admin_url( 'admin-post.php' )
				),
			);
		}

		if ( ap_user_can_permanent_delete( $this->get_id() ) ) {
			$default_options['more']['sub_options']['delete'] = array(
				'text' => __( 'Delete', 'anspress-question-answer' ),
				'href'  => add_query_arg(
					array(
						'action'    => 'anspress_post_action',
						'ap_action' => 'delete_post',
						'id'        => $this->get_id(),
						'__nonce'   => wp_create_nonce( 'delete_post-' . $this->get_id() ),
					),
					admin_url( 'admin-post.php' )
				),
			);
		}

		if ( isset( $options['more'], $options['more']['sub_options'] ) ) {
			$more = wp_parse_args( $options['more']['sub_options'], $default_options['more']['sub_options'] );
			unset( $options['more'] );
		}
		$options = wp_parse_args( $options, $default_options );

		if ( isset( $more ) ) {
			$options['more']['sub_options'] = $more;
		}

		$options = apply_filters( 'ap_post_options', $options );

		return ap_sort_array_by_order( $options );
	}

	public function the_post_options() {
		$options = $this->get_post_options();

		if ( ! empty( $options ) ) {
			echo '<div class="ap-post-options">';
			foreach ( $this->get_post_options() as $key => $args ) {

				$href    = ! empty( $args['href'] ) ? $args['href'] : '#';
				$has_sub = isset( $args['sub_options'] );

				echo '<div' . ( $has_sub ? ' class="ap-dropdown"' : '' ) . '>';
				echo '<a href="' . esc_url( $href ) . '" class="ap-post-option-' . esc_attr( $key ) . ( $has_sub ? '  ap-dropdown-toggle' : '' ) . '"' . ( isset( $args['title'] ) ? ' title="' . esc_attr( $args['title'] ) . '"' : '' );

				if ( isset( $args['ajaxbtn'] ) ) {
					$json = wp_json_encode( $args['ajaxbtn'] );
					echo 'apajaxbtn apquery="' . esc_js( $json ) . '"';
				}
				echo '>' . esc_html( $args['text'] ) . '</a>';

				if ( $has_sub ) {
					echo '<div class="ap-dropdown-menu">';
					foreach ( $args['sub_options'] as $sub_k => $sub_args ) {
						$sub_href = ! empty( $sub_args['href'] ) ? $sub_args['href'] : '#';
						echo '<div><a href="' . esc_url( $sub_href ) . '" class="ap-post-option-' . esc_attr( $sub_k ) . '"';

						if ( isset( $sub_args['ajaxbtn'] ) ) {
							$json = wp_json_encode( $sub_args['ajaxbtn'] );
							echo 'apajaxbtn apquery="' . esc_js( $json ) . '"';
						}
						echo '>' . esc_html( $sub_args['text'] ) . '</a></div>';
					}
					echo '</div>';
				}
				echo '</div>';
			}
			echo '</div>';
		}
	}

	public function can_edit( $user_id = false ) {
		if ( false === $user_id ) {
			$user_id = get_current_user_id();
		}

		return ap_user_can_edit_post( $this->get_id(), $user_id );
	}
}
