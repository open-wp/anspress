<?php
/**
 * An AnsPress add-on to for displaying user profile.
 *
 * @author     Rahul Aryan <support@anspress.io>
 * @copyright  2014 AnsPress.io & Rahul Aryan
 * @license    GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link       https://anspress.io
 * @package    AnsPress
 * @subpackage User Profile Addon
 *
 * @anspress-addon
 * Addon Name:    User Profile
 * Addon URI:     https://anspress.io
 * Description:   Display user profile.
 * Author:        Rahul Aryan
 * Author URI:    https://anspress.io
 */

namespace AnsPress\Addons;
use AnsPress\Shortcodes;
use AnsPress\Addons\Profile;
use AnsPress\Template;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Load helper functions.
require_once ANSPRESS_ADDONS_DIR . '/profile/helpers.php';

/**
 * User profile hooks.
 */
class Profile extends \AnsPress\Singleton {
	/**
	 * Instance of this class.
	 *
	 * @var     object
	 * @since 4.1.8
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since 4.0.0
	 */
	protected function __construct() {
		ap_add_default_options( [
			'user_page_slug_questions'  => 'questions',
			'user_page_slug_answers'    => 'answers',
			'user_page_title_questions' => __( 'Questions', 'anspress-question-answer' ),
			'user_page_title_answers'   => __( 'Answers', 'anspress-question-answer' ),
		] );

		anspress()->add_action( 'ap_form_addon-profile', $this, 'options' );
		anspress()->add_action( 'init', $this, 'init_hook' );
		anspress()->add_action( 'ap_rewrites', $this, 'rewrite_rules', 10, 3 );
		anspress()->add_action( 'wp_ajax_user_more_answers', $this, 'load_more_answers', 10, 2 );
		anspress()->add_action( 'wp_ajax_nopriv_user_more_answers', $this, 'load_more_answers', 10, 2 );
		anspress()->add_filter( 'wp_title_parts', $this, 'wp_title' );
		//anspress()->add_action( 'the_post', $this, 'filter_page_title' );
		anspress()->add_filter( 'ap_current_page', $this, 'ap_current_page' );
		anspress()->add_filter( 'ap_shortcode_display_current_page', $this, 'shortcode_fallback' );
		anspress()->add_filter( 'ap_template_include_theme_compat', $this, 'template_include_theme_compat' );
		anspress()->add_filter( 'ap_before_profile_settings', $this, 'process_public_profile_form' );
		anspress()->add_filter( 'ap_before_profile_settings', $this, 'process_change_password_form' );

		anspress()->add_filter( 'pre_get_avatar_data', $this, 'get_avatar', 1000, 2 );
	}

	/**
	 * Register profile options
	 */
	public function options() {
		$opt = ap_opt();

		$form = array(
			'fields' => array(
				'user_page_title_questions' => array(
					'label' => __( 'Questions page title', 'anspress-question-answer' ),
					'desc'  => __( 'Custom title for user profile questions page', 'anspress-question-answer' ),
					'value' => $opt['user_page_title_questions'],
				),
				'user_page_slug_questions'  => array(
					'label' => __( 'Questions page slug', 'anspress-question-answer' ),
					'desc'  => __( 'Custom slug for user profile questions page', 'anspress-question-answer' ),
					'value' => $opt['user_page_slug_questions'],
				),
				'user_page_title_answers'   => array(
					'label' => __( 'Answers page title', 'anspress-question-answer' ),
					'desc'  => __( 'Custom title for user profile answers page', 'anspress-question-answer' ),
					'value' => $opt['user_page_title_answers'],
				),
				'user_page_slug_answers'    => array(
					'label' => __( 'Answers page slug', 'anspress-question-answer' ),
					'desc'  => __( 'Custom slug for user profile answers page', 'anspress-question-answer' ),
					'value' => $opt['user_page_slug_answers'],
				),
			),
		);

		return $form;
	}

	/**
	 * Init actions.
	 *
	 * @since 4.2.0
	 */
	public function init_hook() {
		add_shortcode( 'anspress_profile', [ self::$instance, 'shortcode_profile' ] );
	}

	/**
	 * Register profile shortcode.
	 *
	 * @since 4.2.0
	 */
	public function shortcode_profile( $attr = [], $content = '' ) {
		$shortcode = Shortcodes::get_instance();

		$shortcode->start( 'profile' );

		/**
		 * Action called before profile page (shortcode) is rendered.
		 *
		 * @since 4.2.0
		 */
		do_action( 'ap_after_display_profile' );

		ap_get_template_part( 'profile/index' );

		/**
		 * Action called after profile page (shortcode) is rendered.
		 *
		 * @since 4.2.0
		 */
		do_action( 'ap_after_display_profile' );

		return $shortcode->end();
	}

	/**
	 * Add category pages rewrite rule.
	 *
	 * @param  array   $rules AnsPress rules.
	 * @param  string  $slug Slug.
	 * @param  integer $base_page_id Base page ID.
	 * @return array
	 */
	public function rewrite_rules( $rules, $slug, $base_page_id ) {
		$base_slug = get_page_uri( ap_opt( 'user_page' ) );
		update_option( 'ap_user_path', $base_slug, true );

		$new_rules = [];
		$new_rules = array(
			$base_slug . '/([^/]+)/([^/]+)/page/?([0-9]{1,})/?'               => 'index.php?ap_user_name=$matches[#]&profile_page=$matches[#]&paged=$matches[#]',
			$base_slug . '/([^/]+)/([^/]+)/answer-page-([0-9]{1,})/?' => 'index.php?ap_user_name=$matches[#]&profile_page=$matches[#]&paged=$matches[#]',
			$base_slug . '/([^/]+)/([^/]+)/?'                                 => 'index.php?ap_user_name=$matches[#]&profile_page=$matches[#]',
			$base_slug . '/([^/]+)/?'                                         => 'index.php?ap_user_name=$matches[#]',
		);

		return $new_rules + $rules;
	}

	/**
	 * Modify user page titles.
	 *
	 * @return string
	 */
	public function user_page_title() {
		$title     = ap_user_display_name( ap_get_displayed_user_id() );
		$user_page = Profile\current_page();
		$pages     = Profile\pages();

		if ( ! empty( $pages[ $user_page ]['title'] ) ) {
			$title = $title . ' | ' . esc_attr( $pages[ $user_page ]['title'] );
		}

		return $title;
	}

	/**
	 * Add user page title.
	 *
	 * @param  array $title AnsPress page title.
	 * @return string
	 */
	public function wp_title( $title ) {
		if ( ap_current_page( 'profile' ) ) {
			$title[1] = $this->user_page_title();
		}

		return $title;
	}

	/**
	 * Filter user page title.
	 *
	 * @param object $_post WP post object.
	 * @return void
	 */
	public function filter_page_title( $_post ) {
		if ( ap_current_page( 'profile' ) && ap_opt( 'user_page' ) == $_post->ID && ! is_admin() ) {
			$_post->post_title = $this->user_page_title();
		}
	}

	/**
	 * Display user questions page.
	 */
	public function question_page() {
		$user_id                        = ap_current_user_id();
		$args['ap_current_user_ignore'] = true;
		$args['author']                 = $user_id;

		/**
		* Filter authors question list args
		*
		* @var array
		*/
		$args = apply_filters( 'ap_authors_questions_args', $args );

		anspress()->questions = new \Question_Query( $args );

		include ap_get_theme_location( 'addons/user/questions.php' );
	}

	/**
	 * Display user questions page.
	 */
	public function answer_page() {
		global $answers;

		$user_id = ap_current_user_id();

		$args['ap_current_user_ignore'] = true;
		$args['ignore_selected_answer'] = true;
		$args['author']                 = $user_id;

		/*
		if ( false !== $paged ) {
			$args['paged'] = $paged;
		}*/

		/**
		 * Filter authors question list args
		 *
		 * @var array
		 */
		$args = apply_filters( 'ap_user_answers_args', $args );

		ap_get_answers( $args );

		ap_get_template_part( 'addons/user/answers' );
	}

	/**
	 * Ajax callback for loading more answers.
	 *
	 * @return void
	 */
	public function load_more_answers() {

		$user_id = ap_sanitize_unslash( 'user_id', 'r' );
		$paged   = ap_sanitize_unslash( 'current', 'r', 1 ) + 1;

		$args['ap_current_user_ignore'] = true;
		$args['ignore_selected_answer'] = true;
		$args['showposts']              = 10;
		$args['author']                 = (int) $user_id;

		if ( false !== $paged ) {
			$args['paged'] = $paged;
		}

		/**
		 * Filter authors question list args
		 *
		 * @param array $args WP_Query arguments.
		 */
		$args = apply_filters( 'ap_user_answers_args', $args );

		ob_start();
		if ( ap_get_answers( $args ) ) {
			/* Start the Loop */
			while ( ap_have_answers() ) :
				ap_the_answer();
				ap_get_template_part( 'addons/user/answer-item' );
			endwhile;
		}
		$html = ob_get_clean();

		ap_ajax_json(
			array(
				'success' => true,
				'element' => '#ap-bp-answers',
				'args'    => array(
					'ap_ajax_action' => 'user_more_answers',
					'__nonce'        => wp_create_nonce( 'loadmore-answers' ),
					'type'           => 'answers',
					'current'        => $paged,
					'user_id'        => $user_id,
				),
				'html'    => $html,
			)
		);
	}

	/**
	 * Override current page of AnsPress.
	 *
	 * @param string $query_var Current page name.
	 * @return string
	 * @since 4.1.0
	 */
	public function ap_current_page( $query_var ) {
		global $wp_query;

		if ( ap_is_profile() || 'profile' === $query_var || 'user' === $query_var || 'user' === get_query_var( 'ap_page' ) || 'profile' === get_query_var( 'ap_page' ) ) {
			$query_var = 'profile';
		}

		return $query_var;
	}

	/**
	 * Override user page template.
	 *
	 * @param string $template Template file.
	 * @return string
	 * @since 4.1.0
	 */
	public function page_template( $template ) {
		if ( is_author() && 'user' === get_query_var( 'ap_page' ) ) {
			$user_slug = ap_opt( 'user_page_id' );
			return locate_template( [ 'page-' . $user_slug . '.php', 'page.php' ] );
		}

		return $template;
	}

	/**
	 * Get current user id for AnsPress profile.
	 *
	 * @return integer
	 * @since 4.1.0
	 */
	public function current_user_id() {
		$query_object = get_queried_object();
		$user_id      = get_queried_object_id();

		// Current user id if queried object is not set.
		if ( ! $query_object instanceof \WP_User || empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		return (int) $user_id;
	}

	/**
	 * Fallback for old profile shortcode `[anspress page="profile"]`.
	 *
	 * @since 4.2.0
	 */
	public function shortcode_fallback() {
		if ( ap_current_page( 'profile' ) ) {
			return $this->shortcode_profile();
		}

		return false;
	}

	/**
	 * Template compatibility.
	 *
	 * @since 4.2.0
	 */
	public static function template_include_theme_compat( $template = '' ) {
		if ( ap_current_page( 'profile' ) ) {
			// Ask page.
			$page_id = ap_main_pages_id( 'profile' );

			$page = get_page( $page_id );

			// Replace the content.
			if ( empty( $page->post_content ) ) {
				$new_content = $this->shortcode_profile();
			} else {
				$new_content = apply_filters( 'the_content', $page->post_content );
			}

			// Replace the title.
			if ( empty( $page->post_title ) ) {
				$new_title = __( 'My Profile', 'anspress-question-answer' );
			} else {
				$new_title = apply_filters( 'the_title', $page->post_title );
			}

			ap_theme_compat_reset_post( array(
				'ID'             => ! empty( $page->ID ) ? $page->ID : 0,
				'post_title'     => $this->user_page_title(),
				'post_author'    => 0,
				'post_date'      => 0,
				'post_content'   => $new_content,
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'is_single'      => true,
				'comment_status' => 'closed',
			) );

			// Locate profile page template.
			$new_template = locate_template( [ 'anspress-profile.php' ], false, false );

			// Override default template.
			if ( ! empty( $new_template ) ) {
				$template = $new_template;
			}
		}

		return $template;
	}

	/**
	 * Process update public profile form.
	 *
	 * @return void
	 * @since 4.2.0
	 */
	public function process_public_profile_form() {
		if ( ! ap_isset_post_value( '__update_nonce' ) ) {
			return;
		}

		// Show error if nonce not verified.
		if ( ! wp_verify_nonce( ap_isset_post_value( '__update_nonce' ), 'update_profile' ) ) {
			Template\alert(
				__( 'Failed to update public profile', 'anspress-question-answer' ),
				__( 'Nonce did not matched. Please try again.', 'anspress-question-answer' ),
				'error'
			);
			return;
		}

		$public_profile = ap_isset_post_value( 'public_profile' );

		$user_id = wp_update_user( [
			'ID'           => get_current_user_id(),
			'first_name'   => $public_profile['first_name'],
			'last_name'    => $public_profile['last_name'],
			'display_name' => $public_profile['nickname'],
			'nickname'     => $public_profile['nickname'],
			'description'  => $public_profile['bio'],
			'user_url'     => $public_profile['user_url'],
		] );

		if ( is_wp_error( $user_id ) ) {
			Template\alert(
				__( 'Failed to update public profile', 'anspress-question-answer' ),
				$user_id->get_error_message(),
				'error'
			);
		} else {
			Template\alert(
				__( 'Successfully updated public profile', 'anspress-question-answer' )
			);
		}

	}

	public function process_change_password_form() {
		if ( ! ap_isset_post_value( '__nonce_password' ) ) {
			return;
		}

		// Show error if nonce not verified.
		if ( ! wp_verify_nonce( ap_isset_post_value( '__nonce_password' ), 'password_nonce' ) ) {
			Template\alert(
				__( 'Failed to update password.', 'anspress-question-answer' ),
				__( 'Nonce did not matched. Please try again.', 'anspress-question-answer' ),
				'error'
			);
			return;
		}

		$user = get_user_by( 'id', get_current_user_id() );

		$old_password = ap_isset_post_value( 'ap-old-password' );
		$password1    = ap_isset_post_value( 'ap-password1' );
		$password2    = ap_isset_post_value( 'ap-password2' );

		if ( empty( $old_password ) || empty( $password1 ) || empty( $password2 ) ) {
			Template\alert(
				__( 'Failed to update password.', 'anspress-question-answer' ),
				__( 'Password field must not be empty.', 'anspress-question-answer' ),
				'error'
			);
			return;
		}

		if ( $user && wp_check_password( $old_password, $user->data->user_pass, $user->ID ) ) {

			// Check password 1 and 2 are matching.
			if ( $password1 !== $password2 ) {
				Template\alert(
					__( 'Failed to update password.', 'anspress-question-answer' ),
					__( 'Confirm password did not matched, please try again.', 'anspress-question-answer' ),
					'error'
				);
				return;
			}

			// All done now update password.
			wp_set_password( $password1, get_current_user_id() );

			Template\alert(
				__( 'Successfully updated password.', 'anspress-question-answer' )
			);

		} else {
			Template\alert(
				__( 'Failed to update password.', 'anspress-question-answer' ),
				__( 'Old password did not matched.', 'anspress-question-answer' ),
				'error'
			);

			return;
		}
	}

	/**
	 * Replace gravatar with locally uploaded avatar.
	 *
	 * @param array $args Arguments.
	 * @param mixed $id_or_email User identifier.
	 * @return array
	 * @since 4.2.0
	 */
	public function get_avatar( $args, $id_or_email ) {
		if ( is_object( $id_or_email ) && ! empty( $id_or_email->user_id ) ) {
			$user_id = (int) $id_or_email->user_id;
		} elseif ( is_object( $id_or_email ) && $id_or_email instanceof WP_user ) {
			$user_id = $id_or_email->ID;
		} elseif ( is_object( $id_or_email ) && $id_or_email instanceof WP_Comment ) {
			$user_id = $id_or_email->user_id;
		} elseif ( is_numeric( $id_or_email ) && ! empty( $id_or_email ) ) {
			$user_id = $id_or_email;
		} else {
			return $args;
		}

		$meta = get_user_meta( $user_id, 'ap_avatar', true );

		if ( empty( $meta ) ) {
			return $args;
		}

		$upload_dir = wp_upload_dir();
		$args['url'] = $upload_dir['baseurl'] . '/' . $meta;

		return $args;
	}

}

// Init addon.
Profile::init();
