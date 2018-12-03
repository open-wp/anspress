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
	 * Stores data about status changes so relevant hooks can be fired.
	 *
	 * @var bool|array
	 */
	protected $status_transition = false;

	/**
	 * Question Data array.
	 *
	 * @var array
	 */
	protected $data = [
		'title'                 => '',
		'content'               => '',
		'author_id'             => 0,
		'parent_id'             => 0,
		'status'                => 'draft',
		'date_created'          => null,
		'date_modified'         => null,
		'version'               => 0,
		'last_active'           => null,
		'last_activity'         => 'posted',
		'last_activity_user_id' => 0,
		'answer_counts'         => 0,
		'vote_up_counts'        => 0,
		'vote_down_counts'      => 0,
		'vote_net_counts'       => 0,
		'best_answer_id'        => 0,
		'view_counts'           => 0,
		'is_featured'           => false,
	];

	/**
	 * All default meta keys to load and map.
	 *
	 * @var array
	 */
	protected $meta_props = [
		'_ap_version'               => 'version',
		'_ap_last_active'           => 'last_active',
		'_ap_last_activity'         => 'last_activity',
		'_ap_last_activity_user_id' => 'last_activity_user_id',
		'_ap_answer_counts'         => 'answer_counts',
		'_ap_vote_up_counts'        => 'vote_up_counts',
		'_ap_vote_down_counts'      => 'vote_down_counts',
		'_ap_vote_net_counts'       => 'vote_net_counts',
		'_ap_best_answer_id'        => 'best_answer_id',
		'_ap_view_counts'           => 'view_counts',
		'_ap_is_featured'           => 'is_featured',
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

			// TODO: apply same post status to answers.
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
			$this->set_status( $new_status );
			$this->save();
		} catch ( Exception $e ) {
			// TODO: Add logging.
			return false;
		}

		return true;
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
	 * Set question content.
	 *
	 * @param string $value Value.
	 * @return void
	 */
	public function set_content( $value ) {
		$this->set_prop( 'content', $value );
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
		return $this->get_prop( 'is_featured', $context );
	}

	/**
	 * Check if question is set as featured.
	 *
	 * @return boolean
	 */
	public function is_featured() {
		return (bool) $this->get_is_featured();
	}

	/**
	 * Check if question has best answer selected.
	 *
	 * @return boolean
	 */
	public function is_solved() {
		return $this->get_best_answer_id() > 0;
	}

}
