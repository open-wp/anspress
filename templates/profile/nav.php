<?php
/**
 * Template for displaying profile page navigation.
 *
 * @license    https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @author     Rahul Aryan <support@anspress.io>
 *
 * @link       https://anspress.io
 * @since      4.2.0
 * @package    AnsPress
 * @subpackage Templates
 */

// Prevent direct access to file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AnsPress\Addons\Profile;

$user_id = ap_get_displayed_user_id();

?>
<nav class="ap-profile-nav" aria-labelledby="<?php esc_attr_e( 'User navigation', 'anspress-question-answer' ); ?>">
	<div class="ap-profile-vcard">
		<div class="ap-profile-avatar">
			<?php echo get_avatar( $user_id, 90 ); ?>
		</div>

		<div class="ap-profile-vcard-name">
			<?php echo ap_user_display_name( $user_id ); ?>
		</div>

		<?php
			/**
			 * Action triggered in profile vcard.
			 *
			 * @since 4.2.0
			 */
			do_action( 'ap_profile_vcard' );
		?>
	</div>

	<ul id="ap-profile-nav-ul" role="menubar">
		<?php foreach ( Profile\nav_links() as $nav ) : ?>
			<li id="<?php echo esc_attr( $nav['id'] ); ?>" class="<?php echo esc_attr( $nav['class'] ); ?>">
				<a href="<?php echo esc_url( $nav['link'] ); ?>">

					<?php
					// Show icon.
					if ( ! empty( $nav['icon'] ) ) {
						echo '<i class="' . esc_attr( $nav['icon'] ) . '"></i>';
					}

					echo esc_html( $nav['title'] );

					// Show count.
					if ( ! empty( $nav['count'] ) ) {
						echo '<span class="ap-profile-nav-count">' . esc_html( $nav['count'] ) . '</span>';
					}
					?>

				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
