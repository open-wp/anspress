<?php
/**
 * User profile template.
 *
 * @license    https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @author     Rahul Aryan <support@anspress.io>
 *
 * @link       https://anspress.io
 * @since      4.2.0
 * @package    AnsPress
 * @subpackage Templates
 */

use AnsPress\Addons\Profile;

// Prevent direct access to file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user_id     = ap_get_displayed_user_id();
$current_tab = ap_sanitize_unslash( 'tab', 'r', 'questions' );
?>

<div id="ap-profile" class="ap-profile">
	<div class="ap-flex-row<?php echo is_active_sidebar( 'anspress-profile' ) ? ' ap-sidebar-active' : ''; ?>">

		<?php ap_get_template_part( 'profile/nav' ); ?>

		<div class="ap-col-main">
			<?php Profile\profile_page_content(); ?>
		</div>

		<?php if ( is_active_sidebar( 'anspress-profile' ) ) : ?>
			<div class="ap-col-sidebar ap-sidebar-profile">
				<?php dynamic_sidebar( 'anspress-profile' ); ?>
			</div>
		<?php endif; ?>
	</div>

</div>
