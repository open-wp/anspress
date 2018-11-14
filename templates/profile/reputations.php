<?php
/**
 * Profile overview template.
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

$reputations = new \AnsPress_Reputation_Query( [ 'user_id' => $user_id ] );
?>

<div class="ap-profile-reps">

	<div class="ap-reps">
		<?php while ( $reputations->have() ) : $reputations->the_reputation(); ?>

			<?php ap_get_template_part( 'profile/reputation', [ 'reputations' => $reputations ] ); ?>

		<?php endwhile; ?>
	</div>

	<?php if ( $reputations->total_pages > 1 ) : ?>
		<?php
			$args = wp_json_encode(
				array(
					'action'  => 'load_more_reputation',
					'__nonce' => wp_create_nonce( 'load_more_reputation' ),
					'current' => 1,
					'user_id' => $reputations->args['user_id'],
				)
			);
		?>
		<a href="#" ap-loadmore="<?php echo esc_js( $args ); ?>" class="ap-loadmore ap-btn" ><?php esc_attr_e( 'Load More', 'anspress-question-answer' ); ?></a>
	<?php endif; ?>

</div>
