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


?>



<div class="ap-reps-item">
	<div class="ap-reps-item-icon"><i class="<?php $reputations->the_icon(); ?> <?php $reputations->the_event(); ?>"></i></div>

	<div class="ap-reps-item-event">

		<div class="ap-display-flex justify-space-betw">
			<div>
				<div class="ap-reputation-activity"><?php $reputations->the_activity(); ?></div>
				<div class="ap-reps-item-date"><?php $reputations->the_date(); ?></div>
			</div>
			<div class="ap-reps-item-points">
				<span><?php $reputations->the_points(); ?></span>
			</div>
		</div>

		<?php $reputations->the_ref_content(); ?>
	</div>
</div>

