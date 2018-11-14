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
?>

<div class="ap-profile-overview">
	<div class="ap__counts ap-display-flex justify-space-betw">

		<div class="ap__question">
			<i class="apicon-question"></i>
			<span>20 Questions</span>
			<span class="ap-text-color1">3 Solved</span>
		</div>

		<div class="ap__answer">
			<i class="apicon-answer"></i>
			<span>20 Answers</span>
			<span class="ap-text-color1">5 Best Answers</span>
		</div>

		<div class="ap__reputation">
			<i class="apicon-reputation"></i>
			<span>4.2k Reputation</span>
		</div>

		<div class="ap__comments">
			<i class="apicon-comments"></i>
			<span>26 Comments</span>
		</div>

	</div>

	<?php ap_get_template_part( 'profile/overview-activities' ); ?>
</div>
