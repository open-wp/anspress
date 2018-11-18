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
<div id="ap-profile-nav-ul" class="ap-tab" role="menubar">
	<?php foreach ( Profile\nav_links() as $nav ) : ?>
		<div id="<?php echo esc_attr( $nav['id'] ); ?>" class="ap-tab__item <?php echo esc_attr( $nav['class'] ); ?>">
			<a href="<?php echo esc_url( $nav['link'] ); ?>" class="ap-tab__anchor">

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
			</div>
	<?php endforeach; ?>
</div>
