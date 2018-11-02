<?php
/**
 * Add tags support in AnsPress questions.
 *
 * @author       Rahul Aryan <support@anspress.io>
 * @copyright    2014 AnsPress.io & Rahul Aryan
 * @license      GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link         https://anspress.io
 * @package      AnsPress
 * @subpackage   Tags Addon
 */

namespace AnsPress\Addons;
use AnsPress\Shortcodes;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Tags addon for AnsPress
 */
class Tags extends \AnsPress\Singleton {
	/**
	 * Instance of this class.
	 *
	 * @var     object
	 * @since 4.1.8
	 */
	protected static $instance = null;

	/**
	 * Initialize the class
	 *
	 * @since 4.1.8 Added filter `ap_category_questions_args`.
	 */
	protected function __construct() {
		anspress()->add_action( 'ap_form_addon-tags', $this, 'option_fields' );
		anspress()->add_action( 'widgets_init', $this, 'widget_positions' );
		anspress()->add_action( 'init', $this, 'register_question_tag', 1 );
		anspress()->add_action( 'ap_admin_menu', $this, 'admin_tags_menu' );
		anspress()->add_action( 'ap_display_question_metas', $this, 'ap_display_question_metas', 10, 2 );
		anspress()->add_action( 'ap_question_info', $this, 'ap_question_info' );
		anspress()->add_action( 'ap_assets_js', $this, 'ap_assets_js' );
		anspress()->add_action( 'ap_enqueue', $this, 'ap_localize_scripts' );
		anspress()->add_filter( 'term_link', $this, 'term_link_filter', 10, 3 );
		anspress()->add_action( 'ap_question_form_fields', $this, 'ap_question_form_fields' );
		anspress()->add_action( 'ap_processed_new_question', $this, 'after_new_question', 0, 2 );
		anspress()->add_action( 'ap_processed_update_question', $this, 'after_new_question', 0, 2 );
		anspress()->add_filter( 'ap_page_title', $this, 'page_title' );
		anspress()->add_filter( 'ap_breadcrumbs', $this, 'ap_breadcrumbs' );
		anspress()->add_action( 'wp_ajax_ap_tags_suggestion', $this, 'ap_tags_suggestion' );
		anspress()->add_action( 'wp_ajax_nopriv_ap_tags_suggestion', $this, 'ap_tags_suggestion' );
		anspress()->add_action( 'ap_rewrites', $this, 'rewrite_rules', 10, 3 );
		anspress()->add_filter( 'ap_get_questions_default_args', $this, 'questions_args' );
		anspress()->add_filter( 'ap_current_page', $this, 'ap_current_page' );
		anspress()->add_action( 'posts_pre_query', $this, 'modify_query_archive', 9999, 2 );

		anspress()->add_filter( 'get_current_questions_filters', $this, 'sorting_filters' );
		anspress()->add_action( 'ap_questions_sort_filters_col3', $this, 'sort_filters_col3' );

		anspress()->add_filter( 'ap_shortcode_display_current_page', $this, 'shortcode_fallback' );
		anspress()->add_filter( 'ap_template_include_theme_compat', $this, 'template_include_theme_compat' );
	}

	/**
	 * Register widget sidebars.
	 */
	public function widget_positions() {
		register_sidebar( array(
			'name'          => __( '(AnsPress) Tag Archive', 'anspress-question-answer' ),
			'description'   => __( 'Sidebar is shown in question tag archive.', 'anspress-question-answer' ),
			'id'            => 'anspress-tag',
			'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
			'after_widget'  => '</div>',
		) );

		register_sidebar( array(
			'name'          => __( '(AnsPress) Tags Page', 'anspress-question-answer' ),
			'description'   => __( 'Sidebar is shown in tags page.', 'anspress-question-answer' ),
			'id'            => 'anspress-tags',
			'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
			'after_widget'  => '</div>',
		) );
	}

	/**
	 * Register tag taxonomy for question cpt.
	 *
	 * @return void
	 * @since 2.0
	 */
	public function register_question_tag() {
		ap_add_default_options( [
			'max_tags'      => 5,
			'min_tags'      => 1,
			'tags_per_page' => 20,
			'tag_page_slug' => 'tag',
		] );

		$tag_labels = array(
			'name'               => __( 'Question Tags', 'anspress-question-answer' ),
			'singular_name'      => _x( 'Tag', 'anspress-question-answer' ),
			'all_items'          => __( 'All Tags', 'anspress-question-answer' ),
			'add_new_item'       => _x( 'Add New Tag', 'anspress-question-answer' ),
			'edit_item'          => __( 'Edit Tag', 'anspress-question-answer' ),
			'new_item'           => __( 'New Tag', 'anspress-question-answer' ),
			'view_item'          => __( 'View Tag', 'anspress-question-answer' ),
			'search_items'       => __( 'Search Tag', 'anspress-question-answer' ),
			'not_found'          => __( 'Nothing Found', 'anspress-question-answer' ),
			'not_found_in_trash' => __( 'Nothing found in Trash', 'anspress-question-answer' ),
			'parent_item_colon'  => '',
		);

		/**
		 * Filter ic called before registering question_tag taxonomy
		 */
		$tag_labels = apply_filters( 'ap_question_tag_labels', $tag_labels );
		$tag_args   = array(
			'hierarchical' => true,
			'labels'       => $tag_labels,
			'rewrite'      => false,
		);

		/**
		 * Filter ic called before registering question_tag taxonomy
		 */
		$tag_args = apply_filters( 'ap_question_tag_args', $tag_args );

		/**
		 * Now let WordPress know about our taxonomy
		 */
		register_taxonomy( 'question_tag', array( 'question' ), $tag_args );

		$this->register_shortcodes();
	}

	/**
	 * Add tags menu in wp-admin.
	 */
	public function admin_tags_menu() {
		add_submenu_page( 'anspress', __( 'Question Tags', 'anspress-question-answer' ), __( 'Tags', 'anspress-question-answer' ), 'manage_options', 'edit-tags.php?taxonomy=question_tag' );
	}

	/**
	 * Register option fields.
	 */
	public function option_fields() {
		$opt = ap_opt();

		$form = array(
			'fields' => array(
				'tags_per_page' => array(
					'label'       => __( 'Tags to show', 'anspress-question-answer' ),
					'description' => __( 'Numbers of tags to show in tags page.', 'anspress-question-answer' ),
					'subtype'     => 'number',
					'value'       => $opt['tags_per_page'],
				),
				'max_tags'      => array(
					'label'       => __( 'Maximum tags', 'anspress-question-answer' ),
					'description' => __( 'Maximum numbers of tags that user can add when asking.', 'anspress-question-answer' ),
					'subtype'     => 'number',
					'value'       => $opt['max_tags'],
				),
				'min_tags'      => array(
					'label'       => __( 'Minimum tags', 'anspress-question-answer' ),
					'description' => __( 'minimum numbers of tags that user must add when asking.', 'anspress-question-answer' ),
					'subtype'     => 'number',
					'value'       => $opt['min_tags'],
				),
				'tag_page_slug' => array(
					'label' => __( 'Tag page slug', 'anspress-question-answer' ),
					'desc'  => __( 'Slug for tag page', 'anspress-question-answer' ),
					'value' => $opt['tag_page_slug'],
				),
			),
		);

		return $form;
	}


	/**
	 * Append meta display.
	 *
	 * @param  array $metas Display metas.
	 * @param  array $question_id Post ID.
	 * @return array
	 * @since 2.0
	 */
	public function ap_display_question_metas( $metas, $question_id ) {
		if ( ap_post_have_terms( $question_id, 'question_tag' ) ) {
			$metas['tags'] = ap_question_tags_html( array(
				'label'       => '<i class="apicon-tag"></i>',
				'show'        => 1,
				'question_id' => $question_id,
			) );
		}

		return $metas;
	}

	/**
	 * Hook tags after post.
	 *
	 * @param   object $post Post object.
	 * @return  string
	 * @since   1.0
	 */
	public function ap_question_info( $post ) {

		if ( ap_question_have_tags() ) {
			echo '<div class="widget"><span class="ap-widget-title">' . esc_attr__( 'Tags', 'anspress-question-answer' ) . '</span>';
			echo '<div class="ap-post-tags clearfix">' . ap_question_tags_html(
				[
					'list'  => true,
					'label' => '',
				]
			) . '</div></div>'; // WPCS: xss okay.
		}
	}

	/**
	 * Enqueue scripts.
	 *
	 * @param array $js Javacript array.
	 * @return array
	 */
	public function ap_assets_js( $js ) {
		$js['tags'] = [
			'dep'    => [ 'anspress-main' ],
			'footer' => true,
		];

		return $js;
	}

	/**
	 * Add translated strings to the javascript files
	 */
	public function ap_localize_scripts() {
		$l10n_data = array(
			'deleteTag'            => __( 'Delete Tag', 'anspress-question-answer' ),
			'addTag'               => __( 'Add Tag', 'anspress-question-answer' ),
			'tagAdded'             => __( 'added to the tags list.', 'anspress-question-answer' ),
			'tagRemoved'           => __( 'removed from the tags list.', 'anspress-question-answer' ),
			'suggestionsAvailable' => __( 'Suggestions are available. Use the up and down arrow keys to read it.', 'anspress-question-answer' ),
		);

		wp_localize_script(
			'anspress-tags',
			'apTagsTranslation',
			$l10n_data
		);
	}

	/**
	 * Filter tag term link.
	 *
	 * @param  string $url      Default URL of taxonomy.
	 * @param  array  $term     Term array.
	 * @param  string $taxonomy Taxonomy type.
	 * @return string           New URL for term.
	 */
	public function term_link_filter( $url, $term, $taxonomy ) {
		if ( 'question_tag' === $taxonomy ) {
			if ( get_option( 'permalink_structure' ) != '' ) {
				$opt = get_option( 'ap_tags_path', 'tags' );
				return home_url( $opt ) . '/' . $term->slug . '/';
			} else {
				return add_query_arg(
					[
						'ap_page'      => 'tag',
						'question_tag' => $term->slug,
					], home_url()
				);
			}
		}
		return $url;
	}

	/**
	 * Add tag field in question form.
	 *
	 * @param array $form AnsPress form arguments.
	 * @since 4.1.0
	 */
	public function ap_question_form_fields( $form ) {
		$editing_id = ap_editing_post_id();

		$form['fields']['tags'] = array(
			'label'      => __( 'Tags', 'anspress-question-answer' ),
			'desc'       => sprintf(
				// Translators: %1$d contain minimum tags required and %2$d contain maximum tags allowed.
				__( 'Tagging will helps others to easily find your question. Minimum %1$d and maximum %2$d tags.', 'anspress-question-answer' ),
				ap_opt( 'min_tags' ),
				ap_opt( 'max_tags' )
			),
			'type'       => 'tags',
			'array_max'  => ap_opt( 'max_tags' ),
			'array_min'  => ap_opt( 'min_tags' ),
			'js_options' => array(
				'create' => true,
			),
		);

		// Add value when editing post.
		if ( ! empty( $editing_id ) ) {
			$tags = get_the_terms( $editing_id, 'question_tag' );
			if ( $tags ) {
				$tags                            = wp_list_pluck( $tags, 'term_id' );
				$form['fields']['tags']['value'] = $tags;
			}
		}

		return $form;
	}

	/**
	 * Things to do after creating a question.
	 *
	 * @param  integer $post_id Post ID.
	 * @param  object  $post Post object.
	 * @since 1.0
	 */
	public function after_new_question( $post_id, $post ) {
		$values = anspress()->get_form( 'question' )->get_values();
		if ( isset( $values['tags'] ) ) {
			wp_set_object_terms( $post_id, $values['tags']['value'], 'question_tag' );
		}
	}

	/**
	 * Tags page title.
	 *
	 * @param  string $title Title.
	 * @return string
	 */
	public function page_title( $title ) {
		if ( is_question_tags() ) {
			$title = ap_opt( 'tags_page_title' );
		} elseif ( is_question_tag() ) {
			$tag_id = sanitize_title( get_query_var( 'q_tag' ) );
			$tag = get_term_by( 'slug', $tag_id, 'question_tag' ); // @codingStandardsIgnoreLine.
			$title  = $tag->name;
		}

		return $title;
	}

	/**
	 * Hook into AnsPress breadcrums to show tags page.
	 *
	 * @param  array $navs Breadcrumbs navs.
	 * @return array
	 */
	public function ap_breadcrumbs( $navs ) {

		if ( is_question_tag() ) {
			$tag_id       = sanitize_title( get_query_var( 'q_tag' ) );
			$tag = get_term_by( 'slug', $tag_id, 'question_tag' ); // @codingStandardsIgnoreLine.
			$navs['page'] = array(
				'title' => __( 'Tags', 'anspress-question-answer' ),
				'link'  => ap_get_link_to( 'tags' ),
				'order' => 8,
			);

			if ( $tag ) {
				$navs['tag'] = array(
					'title' => $tag->name,
					'link'  => get_term_link( $tag, 'question_tag' ), // @codingStandardsIgnoreLine.
					'order' => 8,
				);
			}
		} elseif ( is_question_tags() ) {
			$navs['page'] = array(
				'title' => __( 'Tags', 'anspress-question-answer' ),
				'link'  => ap_get_link_to( 'tags' ),
				'order' => 8,
			);
		}

		return $navs;
	}

	/**
	 * Handle tags suggestion on question form
	 */
	public function ap_tags_suggestion() {
		$keyword = ap_sanitize_unslash( 'q', 'r' );

		$tags = get_terms(
			'question_tag', array(
				'orderby'    => 'count',
				'order'      => 'DESC',
				'hide_empty' => false,
				'search'     => $keyword,
				'number'     => 8,
			)
		);

		if ( $tags ) {
			$items = array();
			foreach ( $tags as $k => $t ) {
				$items [ $k ] = $t->slug;
			}

			$result = array(
				'status' => true,
				'items'  => $items,
			);
			wp_send_json( $result );
		}

		wp_send_json( array( 'status' => false ) );
	}

	/**
	 * Add category pages rewrite rule.
	 *
	 * @param  array $rules AnsPress rules.
	 * @return array
	 */
	public function rewrite_rules( $rules, $slug, $base_page_id ) {
		$base_slug = get_page_uri( ap_opt( 'tags_page' ) );
		update_option( 'ap_tags_path', $base_slug, true );

		$cat_rules = array(
			$base_slug . '/([^/]+)/page/?([0-9]{1,})/?$' => 'index.php?question_tag=$matches[#]&paged=$matches[#]&ap_page=tag',
			$base_slug . '/([^/]+)/?$'                   => 'index.php?question_tag=$matches[#]&ap_page=tag',
		);

		return $cat_rules + $rules;
	}

	/**
	 * Filter main questions query args. Modify and add tag args.
	 *
	 * @param  array $default Questions args.
	 * @return array
	 */
	public function questions_args( $default ) {
		$current_tag = ap_isset_post_value( 'ap_tag' );

		if ( ! empty( $current_tag ) ) {
			$default['tax_query'][] = array(
				'taxonomy' => 'question_tag',
				'field'    => 'id',
				'terms'    => [ $current_tag ],
			);
		}

		return $default;
	}

	/**
	 * Modify current page to show tag archive.
	 *
	 * @param string $query_var Current page.
	 * @return string
	 * @since 4.1.0
	 */
	public function ap_current_page( $query_var ) {
		global $wp_query;

		if ( ap_is_tag() || 'tag' === $query_var || 'tag' === get_query_var( 'ap_page' ) ) {
			return 'tag';
		} elseif ( 'tags' === $query_var ) {
			return 'tags';
		}

		return $query_var;
	}

	/**
	 * Modify main query to show tag archive.
	 *
	 * @param array|null $posts Array of objects.
	 * @param object     $query Wp_Query object.
	 *
	 * @return array|null
	 * @since 4.1.0
	 */
	public function modify_query_archive( $posts, $query ) {
		if ( $query->is_main_query() &&
			$query->is_tax( 'question_tag' ) &&
			'tag' === get_query_var( 'ap_page' ) ) {

			$query->found_posts   = 1;
			$query->max_num_pages = 1;
			$page                 = get_page( ap_opt( 'tags_page' ) );
			$page->post_title     = get_queried_object()->name;
			$posts                = [ $page ];
		}

		return $posts;
	}

	/**
	 * Add tag sorting in list filters.
	 *
	 * @return array
	 */
	public function sorting_filters( $filters ) {
		$current_cat = ap_isset_post_value( 'ap_tag' );
		if ( ! empty( $current_cat ) ) {
			$tag = get_term_by( 'term_id', $current_cat, 'question_tag' );
			$filters[] = array(
				'name'  => 'ap_tag',
				'label' => $tag->name,
			);
		}

		return $filters;
	}

	/**
	 * Show category filter dropdown.
	 *
	 * @since 4.2.0
	 */
	public function sort_filters_col3() {
		if ( ap_current_page( 'tag' ) ) {
			return;
		}

		$args = array(
			'show_option_all' => __( 'All tags', 'anspress-question-answer' ),
			'hide_empty'      => 1,
			'selected'        => ap_isset_post_value( 'ap_tag' ),
			'hierarchical'    => 1,
			'name'            => 'ap_tag',
			'id'              => 'ap-filters-tag',
			'class'           => 'ap-filters-tag',
			'depth'           => 0,
			'tab_index'       => 0,
			'taxonomy'        => 'question_tag',
			'hide_if_empty'   => false,
			'value_field'     => 'term_id',
		);

		wp_dropdown_categories( $args );
	}

	/**
	 * Fallback for old tags/tag shortcode `[anspress page="tags"]`.
	 *
	 * @since 4.2.0
	 */
	public function shortcode_fallback() {
		if ( ap_current_page( 'tags' ) ) {
			return $this->shortcode_tags();
		} elseif ( ap_current_page( 'tag' ) ) {
			return $this->shortcode_tag();
		}

		return false;
	}

	/**
	 * Template compatibility.
	 *
	 * @since 4.2.0
	 */
	public static function template_include_theme_compat( $template = '' ) {
		if ( ap_current_page( 'tags' ) ) {
			// Ask page.
			$page_id = ap_main_pages_id( 'tags' );

			$page = get_page( $page_id );

			// Replace the content.
			if ( empty( $page->post_content ) ) {
				$new_content = $this->shortcode_tags();
			} else {
				$new_content = apply_filters( 'the_content', $page->post_content );
			}

			// Replace the title.
			if ( empty( $page->post_title ) ) {
				$new_title = __( 'Tags', 'anspress-question-answer' );
			} else {
				$new_title = apply_filters( 'the_title', $page->post_title );
			}

			ap_theme_compat_reset_post( array(
				'ID'             => ! empty( $page->ID ) ? $page->ID : 0,
				'post_title'     => $new_title,
				'post_author'    => 0,
				'post_date'      => 0,
				'post_content'   => $new_content,
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'is_single'      => true,
				'comment_status' => 'closed',
			) );
		} elseif ( ap_current_page( 'tag' ) ) {
			$tag = get_queried_object();

			ap_theme_compat_reset_post( array(
				'ID'             => 0,
				'post_title'     => $tag->name,
				'post_author'    => 0,
				'post_date'      => 0,
				'post_content'   => $this->shortcode_tag(),
				'post_type'      => 'question',
				'post_status'    => 'publish',
				'is_tax'         => true,
				'is_archive'     => true,
				'is_single'      => false,
				'comment_status' => 'closed',
			) );
		}

		return $template;
	}

	/**
	 * Register shortcodes.
	 *
	 * @since 4.2.0
	 */
	private function register_shortcodes() {
		add_shortcode( 'anspress_tag', [ self::$instance, 'shortcode_tag' ] );
		add_shortcode( 'anspress_tags', [ self::$instance, 'shortcode_tags' ] );
	}

	/**
	 * Register tags shortcode.
	 *
	 * @since 4.2.0
	 */
	public function shortcode_tags() {
		$shortcode = Shortcodes::get_instance();
		return $shortcode->display_custom_shortcode(
			'tags',
			self::$instance,
			'display_shortcode_tags'
		);
	}

	/**
	 * Display tags shortcode.
	 *
	 * @since 4.2.0
	 */
	public function display_shortcode_tags() {
		/**
		 * Action called before tags page (shortcode) is rendered.
		 *
		 * @since 4.2.0
		 */
		do_action( 'ap_before_display_tags' );

		ap_get_template_part( 'tags' );

		/**
		 * Action called after tags page (shortcode) is rendered.
		 *
		 * @since 4.2.0
		 */
		do_action( 'ap_after_display_tags' );
	}

	/**
	 * Register tag shortcode.
	 *
	 * @since 4.2.0
	 */
	public function shortcode_tag() {
		$shortcode = Shortcodes::get_instance();
		return $shortcode->display_custom_shortcode(
			'tag',
			self::$instance,
			'display_shortcode_tag'
		);
	}

	/**
	 * Tag page layout.
	 */
	public function display_shortcode_tag() {
		/**
		 * Action called before tag page (shortcode) is rendered.
		 *
		 * @since 4.2.0
		 */
		do_action( 'ap_before_display_tag' );

		ap_get_template_part( 'content-archive-tag' );

		/**
		 * Action called after tag page (shortcode) is rendered.
		 *
		 * @since 4.2.0
		 */
		do_action( 'ap_after_display_tag' );
	}
}


// Init addons.
Tags::init();
