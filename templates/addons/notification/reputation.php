<?php
/**
 * Notification reputation type template.
 *
 * Render notification item if ref_type is reputation.
 *
 * @author  Rahul Aryan <support@anspress.io>
 * @link    https://anspress.io/
 * @since   4.0.0
 * @package AnsPress
 */

?>
<div class="ap__item">

	<a class="ap__right" href="<?php $this->the_permalink(); ?>">
		<?php $this->the_verb(); ?>
		<time class="ap__date"><?php $this->the_date(); ?></time>
	</a>

	<div class="ap__rep"><?php $this->the_reputation_points(); ?></div>
</div>
