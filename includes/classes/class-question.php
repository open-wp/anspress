<?php
/**
 * Question class.
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

class Question extends Data_Cpt {


	/**
	 * Question Data array.
	 *
	 * @var array
	 */
	protected $data = [
		'title'                    => '',
		'content'                  => '',
		'author_id'                => 0,
		'parent_id'                => 0,
		'status'                   => 'draft',
		'date_created'             => null,
		'date_modified'            => null,
		'comment_count'            => 0,
		'version'                  => 0,
		'last_active'              => null,
		'last_activity'            => 'posted',
		'last_activity_user_id'    => 0,
		'answer_counts'            => 0,
		'vote_up_counts'           => 0,
		'vote_down_counts'         => 0,
		'vote_net_counts'          => 0,
		'best_answer_id'           => 0,
		'view_counts'              => 0,
		'is_featured'              => false,
		'is_closed'                => false,
		'unapproved_comment_count' => false,
	];

	/**
	 * All default meta keys to load and map.
	 *
	 * @var array
	 */
	protected $meta_props = [
		'_ap_version'                  => 'version',
		'_ap_last_active'              => 'last_active',
		'_ap_last_activity'            => 'last_activity',
		'_ap_last_activity_user_id'    => 'last_activity_user_id',
		'_ap_answer_counts'            => 'answer_counts',
		'_ap_vote_up_counts'           => 'vote_up_counts',
		'_ap_vote_down_counts'         => 'vote_down_counts',
		'_ap_vote_net_counts'          => 'vote_net_counts',
		'_ap_best_answer_id'           => 'best_answer_id',
		'_ap_view_counts'              => 'view_counts',
		'_ap_is_featured'              => 'is_featured',
		'_ap_is_closed'                => 'is_closed',
		'_ap_unapproved_comment_count' => 'unapproved_comment_count',
	];

	/**
	 * Stores meta in cache for future reads.
	 *
	 * A group must be set to to enable caching.
	 *
	 * @var string
	 */
	protected $cache_group = 'questions';

	/**
	 * Which data store to load.
	 *
	 * @var string
	 */
	protected $data_store_name = 'question';

	/**
	 * This is the name of this object type.
	 *
	 * @var string
	 */
	protected $object_type = 'question';

	/**
	 * Get the question if ID is passed, otherwise the question is new and empty.
	 *
	 * @param  int|object|AnsPress\Question $question Question to read.
	 */
	public function __construct( $question = 0 ) {
		parent::__construct( $question );

		if ( is_numeric( $question ) && $question > 0 ) {
			$this->set_id( $question );
		} elseif ( $question instanceof self ) {
			$this->set_id( $question->get_id() );
		} elseif ( ! empty( $question->ID ) ) {
			$this->set_id( $question->ID );
		} else {
			$this->set_object_read( true );
		}

		$this->data_store = Data_Store::load( $this->data_store_name );

		if ( $this->get_id() > 0 ) {
			$this->data_store->read( $this );
		}
	}

	/**
	 * Get internal type.
	 *
	 * @return string
	 */
	public function get_type() {
		return 'question';
	}

	/**
	 * Get all class data in array format.
	 *
	 * @return array
	 */
	public function get_data() {
		return array_merge(
			array(
				'id' => $this->get_id(),
			),
			$this->data,
			array(
				'meta_data' => $this->get_meta_data(),
			)
		);
	}

	/**
	 * set data to the database.
	 *
	 * @return int question ID
	 * @todo status_transition
	 */
	public function save() {
		try {
			if ( $this->data_store ) {
				/**
				 * Trigger action before saving question to the DB. Allows you to adjust object props before save.
				 *
				 * @param \AnsPress\Question Instance.
				 * @param \AnsPress\Data_Store Instance.
				 *
				 * @since 4.2.0
				 */
				do_action( 'ap_before_question_object_save', $this, $this->data_store );

				if ( $this->get_id() ) {
					$this->data_store->update( $this );
				} else {
					$this->data_store->create( $this );
				}
			}

			$this->status_transition();
		} catch ( \Exception $e ) {
			// TODO: Log error.
			return new \WP_Error( 'title_empty', $e->getMessage() );
		}

		return $this->get_id();
	}

	/**
	 * Set parent id.
	 *
	 * @param string $value Value.
	 * @return void
	 */
	public function set_parent_id( $value ) {
		$this->set_prop( 'parent_id', absint( $value ) );
	}

	/**
	 * Set question title.
	 *
	 * @param string $value Value.
	 * @return void
	 */
	public function set_title( $value ) {
		$this->set_prop( 'title', $value );
	}

	/**
	 * Set question answer counts.
	 *
	 * @param integer $value Value.
	 * @return void
	 */
	public function set_answer_counts( $value ) {
		$this->set_prop( 'answer_counts', absint( $value ) );
	}

	/**
	 * Set question best answer id.
	 *
	 * @param integer $value Value.
	 * @return void
	 */
	public function set_best_answer_id( $value ) {
		$this->set_prop( 'best_answer_id', absint( $value ) );
	}

	/**
	 * Set question view counts.
	 *
	 * @param integer $value Value.
	 * @return void
	 */
	public function set_view_counts( $value ) {
		$this->set_prop( 'view_counts', absint( $value ) );
	}

	/**
	 * Set is_featured.
	 *
	 * @param boolean $value Value.
	 * @return void
	 */
	public function set_is_featured( $value ) {
		$this->set_prop( 'is_featured', (bool) $value );
	}

	/**
	 * Instantly toggles question `is_featured` meta.
	 *
	 * @return boolean Updated status;
	 */
	public function toggle_featured() {
		$this->set_is_featured( ! $this->is_featured() );
		$this->save();
		return $this->is_featured();
	}

	/**
	 * Set is_closed.
	 *
	 * @param boolean $value Value.
	 * @return void
	 */
	public function set_is_closed( $value ) {
		$this->set_prop( 'is_closed', (bool) $value );
	}

	/**
	 * Instantly toggles question `is_closed` meta.
	 *
	 * @return boolean Updated status;
	 */
	public function toggle_closed() {
		$this->set_is_closed( ! $this->is_closed() );
		$this->save();
		return $this->is_closed();
	}

	/*
	|--------------------------------------------------------------------------
	| Getters
	|--------------------------------------------------------------------------
	*/

	/**
	 * Expands the shipping and billing information in the changes array.
	 */
	public function get_changes() {
		$changed_props = parent::get_changes();
		return $changed_props;
	}

	/**
	 * Get question title.
	 *
	 * @param string $context Context.
	 * @return integer
	 */
	public function get_parent_id( $context = 'view' ) {
		return $this->get_prop( 'parent_id', $context );
	}

	/**
	 * Get numbers of answer for question.
	 *
	 * @param string $context Context.
	 * @return integer
	 */
	public function get_answer_counts( $context = 'view' ) {
		return $this->get_prop( 'answer_counts', $context );
	}

	/**
	 * Get question's best answer id.
	 *
	 * @param string $context Context.
	 * @return integer
	 */
	public function get_best_answer_id( $context = 'view' ) {
		return $this->get_prop( 'best_answer_id', $context );
	}

	/**
	 * Get question view counts.
	 *
	 * @param string $context Context.
	 * @return integer
	 */
	public function get_view_counts( $context = 'view' ) {
		return $this->get_prop( 'view_counts', $context );
	}

	/**
	 * Get question featured status.
	 *
	 * @param string $context Context.
	 * @return boolean
	 */
	public function get_is_featured( $context = 'view' ) {
		// Make sure to return string 1 or 0 when editing.
		if ( 'edit' === $context ) {
			return $this->get_prop( 'is_featured', $context ) ? '1' : '0';
		}
		return $this->get_prop( 'is_featured', $context );
	}

	/**
	 * Get question closed status.
	 *
	 * @param string $context Context.
	 * @return boolean
	 */
	public function get_is_closed( $context = 'view' ) {
		// Make sure to return string 1 or 0 when editing.
		if ( 'edit' === $context ) {
			return $this->get_prop( 'is_closed', $context ) ? '1' : '0';
		}
		return $this->get_prop( 'is_closed', $context );
	}

	/**
	 * Check if question is set as featured.
	 *
	 * @return boolean
	 * @todo replace old function
	 */
	public function is_featured() {
		return (bool) $this->get_is_featured();
	}

	/**
	 * Check if question has best answer selected.
	 *
	 * @return boolean
	 * @todo replace old function
	 */
	public function is_solved() {
		return $this->get_best_answer_id() > 0;
	}

	/**
	 * Check if question is closed.
	 *
	 * @return boolean
	 * @todo use this previous closed status.
	 */
	public function is_closed() {
		return (bool) $this->get_is_closed();
	}

	/**
	 * Get all frontend options for the post.
	 *
	 * @param array $options Options.
	 * @return array
	 */
	public function get_post_options( $options = [] ) {
		// Close question.
		if ( ap_user_can_close_question( $this->get_id() ) ) {
			$options['close'] = array(
				'text'  => $this->is_closed() ? __( 'Open', 'anspress-question-answer' ) : __( 'Close', 'anspress-question-answer' ),
				'href'  => $this->get_close_link(),
				'title' => $this->is_closed() ? __( 'Open question for new answers and questions', 'anspress-question-answer' ) :  __( 'Close question for new comments and answers', 'anspress-question-answer' ),
			);
		}

		if ( ap_user_can_toggle_featured() ) {
			$options['more'] = array(
				'sub_options' => array(
					'toggle_featured' => array(
						'text' => $this->is_featured() ? __( 'Unfeature', 'anspress-question-answer' ) : __( 'Set as featured', 'anspress-question-answer' ),
						'href' => $this->get_toggle_featured_link(),
					),
				),
			);
		}

		return parent::get_post_options( $options );
	}

	/**
	 * Return link to close question.
	 *
	 * @param mixed $_post Post.
	 * @return string
	 * @since 2.0.1
	 */
	function get_close_link() {
		$link = add_query_arg(
			array(
				'action'    => 'anspress_post_action',
				'ap_action' => 'close_question',
				'id'        => $this->get_id(),
				'__nonce'   => wp_create_nonce( 'close-question-' . $this->get_id() ),
			),
			admin_url( 'admin-post.php' )
		);

		/**
		 * Allows filtering post edit link.
		 *
		 * @param string $link Url to edit post.
		 * @since 4.2.0
		 */
		return apply_filters( 'ap_get_close_link', $link );
	}

	/**
	 * Return link to toggle featured question.
	 *
	 * @param mixed $_post Post.
	 * @return string
	 * @since 2.0.1
	 */
	function get_toggle_featured_link() {
		$link = add_query_arg(
			array(
				'action'    => 'anspress_post_action',
				'ap_action' => 'toggle_featured',
				'id'        => $this->get_id(),
				'__nonce'   => wp_create_nonce( 'toggle-featured-' . $this->get_id() ),
			),
			admin_url( 'admin-post.php' )
		);

		/**
		 * Allows filtering post toggle featured link.
		 *
		 * @param string $link Url to toggle featured post.
		 * @since 4.2.0
		 */
		return apply_filters( 'ap_toggle_featured_link', $link );
	}

}
