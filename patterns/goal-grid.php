<?php
/**
 * Title: Ziel grid
 * Slug: fitness-salzkammergut/goal-grid
 * Categories: featured
 * Description: Karten-Grid für Trainingsziele mit Icons.
 */
?>
<!-- wp:group {"className":"ziel-grid","layout":{"type":"constrained"}} -->
<div class="wp-block-group ziel-grid">
	<!-- wp:group {"className":"ziel-grid__cards","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"center","orientation":"horizontal"}} -->
	<div class="wp-block-group ziel-grid__cards">
		<!-- wp:group {"className":"ziel-card ziel-card--focus","layout":{"type":"constrained"}} -->
		<div class="wp-block-group ziel-card ziel-card--focus">
			<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"ziel-card__icon"} -->
			<figure class="wp-block-image size-full ziel-card__icon"><img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/icons/scale.svg' ); ?>" alt=""/></figure>
			<!-- /wp:image -->

			<!-- wp:heading {"level":3,"className":"ziel-card__title"} -->
			<h3 class="ziel-card__title">Abnehmen und<br>Wohlfühlen</h3>
			<!-- /wp:heading -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"ziel-card ziel-card--secondary","layout":{"type":"constrained"}} -->
		<div class="wp-block-group ziel-card ziel-card--secondary">
			<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"ziel-card__icon"} -->
			<figure class="wp-block-image size-full ziel-card__icon"><img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/icons/hand-grip.svg' ); ?>" alt=""/></figure>
			<!-- /wp:image -->

			<!-- wp:heading {"level":3,"className":"ziel-card__title"} -->
			<h3 class="ziel-card__title">Reha und<br>Prävention</h3>
			<!-- /wp:heading -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"ziel-card ziel-card--secondary","layout":{"type":"constrained"}} -->
		<div class="wp-block-group ziel-card ziel-card--secondary">
			<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"ziel-card__icon"} -->
			<figure class="wp-block-image size-full ziel-card__icon"><img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/icons/dumbbell.svg' ); ?>" alt=""/></figure>
			<!-- /wp:image -->

			<!-- wp:heading {"level":3,"className":"ziel-card__title"} -->
			<h3 class="ziel-card__title">Kraft- und<br>Muskelaufbau</h3>
			<!-- /wp:heading -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"ziel-card ziel-card--secondary","layout":{"type":"constrained"}} -->
		<div class="wp-block-group ziel-card ziel-card--secondary">
			<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"ziel-card__icon"} -->
			<figure class="wp-block-image size-full ziel-card__icon"><img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/icons/dart.svg' ); ?>" alt=""/></figure>
			<!-- /wp:image -->

			<!-- wp:heading {"level":3,"className":"ziel-card__title"} -->
			<h3 class="ziel-card__title">Sport und<br>Performance</h3>
			<!-- /wp:heading -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"ziel-card ziel-card--secondary","layout":{"type":"constrained"}} -->
		<div class="wp-block-group ziel-card ziel-card--secondary">
			<!-- wp:image {"sizeSlug":"full","linkDestination":"none","className":"ziel-card__icon"} -->
			<figure class="wp-block-image size-full ziel-card__icon"><img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/icons/treadmill.svg' ); ?>" alt=""/></figure>
			<!-- /wp:image -->

			<!-- wp:heading {"level":3,"className":"ziel-card__title"} -->
			<h3 class="ziel-card__title">Fitness und<br>Gesundheit</h3>
			<!-- /wp:heading -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
