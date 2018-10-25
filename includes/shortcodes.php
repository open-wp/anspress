<?php
/**
 * AnsPress Shortcodes.
 *
 * @author Rahul Aryan <rah12@live.com>
 * @package AnsPress
 * @subpackage Shortcodes
 * @since 4.2.0
 */

namespace AnsPress;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AnsPress Shortcode Class.
 *
 * @since 4.2.0
 */
class Shortcodes {
	/**
	 * The shortcodes list.
	 *
	 * @var array
	 */
	public $codes = array();

	/**
	 * Refers to a single instance of this class.
	 *
	 * @var null|object
	 */
	private static $instance = null;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return \AnsPress\Shortcodes A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * CLass constructor.
	 *
	 * @since 4.2.0
	 */
	private function __construct() {
		$this->codes = array(
			'anspress'          => [ $this, 'display_current_page' ],
			'anspress_archive'  => [ $this, 'display_archive' ],
			'anspress_question' => [ $this, 'display_question' ],
			'anspress_edit'     => [ $this, 'display_edit' ],
			'anspress_ask_form' => [ $this, 'display_ask' ],
		);
	}

	/**
	 * Register the AnsPress shortcodes
	 *
	 * @since 4.2.0
	 */
	public function add_shortcodes() {
		foreach ( (array) $this->codes as $code => $function ) {
			add_shortcode( $code, $function );
		}
	}

	/**
	 * Unset some globals in the $bbp object that hold query related info.
	 */
	private function unset_globals() {
		$ap = anspress();

		// Unset global queries
		$ap->questions_query = new \WP_Query();

		// Unset global ID's
		$ap->current_question_id = 0;
		$ap->current_answer_id   = 0;

		// Reset the post data
		wp_reset_postdata();
	}

	/**
	 * Start an output buffer.
	 *
	 * This is used to put the contents of the shortcode into a variable rather
	 * than outputting the HTML at run-time. This allows shortcodes to appear
	 * in the correct location in the_content() instead of when it's created.
	 */
	private function start( $query_name = '' ) {
		// Set query name
		set_query_var( '_ap_query_name', $query_name );

		// Start output buffer
		ob_start();
		echo '<div id="anspress" class="anspress">';
	}

	/**
	 * Return the contents of the output buffer and flush its contents.
	 */
	private function end() {

		// Unset globals
		$this->unset_globals();

		// Reset the query name
		set_query_var( '_ap_query_name', '' );
		echo '</div>';

		// Return and flush the output buffer
		return ob_get_clean();
	}

	/**
	 * Display current page, fallback for old shortcode.
	 *
	 * @param array $attr
	 * @param string $content
	 * @return string
	 * @since 4.2.0
	 */
	public function display_current_page( $attr = [], $content = '' ) {
		if ( ap_current_page( 'ask' ) ) {
			return $this->display_ask( $attr, $content );
		} else {
			return $this->display_archive();
		}
	}

	/**
	 * Display archive of the question.
	 *
	 * @param array $attr
	 * @param string $content
	 * @return string
	 * @since 4.2.0
	 */
	public function display_archive() {
		// Unset globals
		$this->unset_globals();

		// Start output buffer
		$this->start( 'archive' );

		/**
		 * Action called before displaying archive page shortcode.
		 *
		 * @since 4.2.0
		 */
		do_action( 'ap_before_display_archive' );

		if ( ap_user_can_read_questions() ) {
			ap_get_template_part( 'content-archive-question' );
		} else {
			ap_get_template_part( 'feedback-questions' );
		}

		/**
		 * Action called after displaying archive page shortcode.
		 *
		 * @since 4.2.0
		 */
		do_action( 'ap_after_display_arcive' );

		// Return contents of output buffer
		return $this->end();
	}

	/**
	 * Output single question page.
	 *
	 * @since 4.2.0
	 */
	public function display_question( $attr = [], $content = '' ) {
		$ap = anspress();

		$attr = wp_parse_args( $attr, array(
			'id' => get_question_id(),
		) );

		// Unset globals
		$this->unset_globals();
		$question_id = $ap->current_question_id = $attr['id'];

		// Reset the queries if not in theme compat
		if ( ! ap_is_theme_compat_active() ) {
			// Reset necessary question_query.
			$ap->questions_query->query_vars['post_type'] = 'question';
			$ap->questions_query->in_the_loop             = true;
			$ap->questions_query->post                    = get_post( $question_id );
		}

		$answer_id    = get_query_var( 'answer_id', false );
		$answers_args = [];
		$order_by     = Template\get_current_answer_sorting();

		// Show unpublished answers.
		if ( is_user_logged_in() && 'unpublished' === $order_by ) {
			// If current user can edit other post then don't limit post of specific user.
			if ( ! current_user_can( 'ap_edit_others_answer' ) ) {
				$answers_args['author'] = get_current_user_id();
			}

			$answers_args['post_status'] = [ 'trash', 'moderate', 'future' ];
		}

		// Start output buffer
		$this->start( 'question' );

		/**
		 * Action called before displaying question page shortcode.
		 *
		 * @since 4.2.0
		 */
		do_action( 'ap_before_display_question' );

		if ( false !== $answer_id && ! ap_user_can_read_answer( $answer_id ) ) {
			$ap->answers_query->in_the_loop = true;
			$ap->answers_query->post        = get_post( $answer_id );
			ap_get_template_part( 'feedback-answer' );
		} elseif ( ap_user_can_read_question( $question_id ) ) {

			// Start answers query.
			ap_get_answers( $answers_args );

			ap_get_template_part( 'content-single-question' );
		} else {
			ap_get_template_part( 'feedback-single-question' );
		}

		/**
		 * Action called after displaying question page shortcode.
		 *
		 * @since 4.2.0
		 */
		do_action( 'ap_after_display_question' );

		// Return contents of output buffer
		return $this->end();
	}

	/**
	 * Output edit page.
	 *
	 * @param array $attr {
	 *    @type integer $id Post id to edit.
	 * }
	 * @param string $content Shortcode content.
	 * @since 4.2.0
	 * @todo add feedback and check permissions.
	 */
	public function display_edit( $attr = [], $content = '' ) {
		// Set editing post id as query var.
		if ( ! empty( $attr['id'] ) ) {
			set_query_var( '_ap_editing_post_id', (int) $attr['id'] );
		}

		$post_id = ap_editing_post_id();

		global $editing_post;
		$editing_post = ap_get_post( $post_id );

		// Start output buffer
		$this->start( 'edit' );

		/**
		 * Action called before displaying edit page shortcode.
		 *
		 * @since 4.2.0
		 */
		do_action( 'ap_before_display_edit' );

		if ( empty( $attr['id'] ) && ! ap_verify_nonce( 'edit-post-' . $post_id ) ) {
			echo '<p class="ap-msg-cheating">' . esc_attr__( 'Trying to cheat?!', 'anspress-question-answer' ) . '</p>';
		} else {

			if ( 'question' === $editing_post->post_type ) {
				ap_ask_form();
			} elseif ( 'answer' === $editing_post->post_type ) {
				ap_answer_form( $editing_post->post_parent, true );
			}

		}

		/**
		 * Action called after displaying edit page shortcode.
		 *
		 * @since 4.2.0
		 */
		do_action( 'ap_after_display_edit' );

		// Unset post id.
		if ( ! empty( $attr['id'] ) ) {
			set_query_var( '_ap_editing_post_id', '' );
		}

		// Return contents of output buffer
		return $this->end();
	}

	/**
	 * Output question form.
	 *
	 * @since 4.2.0
	 * @todo Remove old shortcode code and add capability to new shortcode.
	 */
	public function display_ask( $attr = [], $content = '' ) {
		// Start output buffer
		$this->start( 'ask' );

		/**
		 * Action called before ask page (shortcode) is rendered.
		 *
		 * @since 4.2.0
		 */
		do_action( 'ap_before_display_ask' );

		if ( ! ap_user_can_ask() ) {
			ap_get_template_part( 'feedback-ask' );
		} else {
			ap_get_template_part( 'content-ask' );
		}

		/**
		 * Action called after ask page (shortcode) is rendered.
		 *
		 * @since 4.2.0
		 */
		do_action( 'ap_after_display_ask' );

		// Return contents of output buffer
		return $this->end();
	}

}
