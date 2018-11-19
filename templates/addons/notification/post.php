<div class="ap__item">

	<a class="ap__right" href="<?php $this->the_permalink(); ?>">
		<strong class="ap__actor"><?php $this->the_actor(); ?></strong> <?php $this->the_verb(); ?>
		<span class="ap__ref"><?php $this->the_ref_title(); ?></span>
		<time class="ap__date"><?php $this->the_date(); ?></time>
	</a>

	<?php if ( 'vote_down' === $this->object->noti_verb ) : ?>
		<div class="ap__icon <?php $this->the_icon(); ?>"></div>
	<?php else : ?>
		<div class="ap__avatar"><?php $this->the_actor_avatar( 35 ); ?></div>
	<?php endif; ?>
</div>
