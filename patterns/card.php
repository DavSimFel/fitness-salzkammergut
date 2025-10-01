<?php
/**
 * Title: Card
 * Slug: fitness-salzkammergut/card
 * Categories: cards
 * Description: Editorial teaser card with image, chip, heading and meta styled via Tailwind.
 */
?>
<!-- wp:group {"className":"tw-card"} -->
<div class="wp-block-group tw-card">
	<!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"tw-card__media"} -->
	<figure class="wp-block-image size-large tw-card__media"><img src="https://placehold.co/800x600" alt=""/></figure>
	<!-- /wp:image -->

	<!-- wp:group {"className":"tw-card__body tw-gap-4","layout":{"type":"flex","orientation":"vertical"}} -->
	<div class="wp-block-group tw-card__body tw-gap-4">
		<!-- wp:paragraph {"className":"tw-chip"} -->
		<p class="tw-chip">Kategorie</p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":3,"className":"tw-font-semibold tw-leading-snug","fontSize":"xl"} -->
		<h3 class="tw-font-semibold tw-leading-snug has-xl-font-size">Headline für diesen Inhalt</h3>
		<!-- /wp:heading -->

<!-- wp:paragraph {"className":"tw-leading-relaxed","fontSize":"base","textColor":"grey"} -->
<p class="tw-leading-relaxed has-grey-color has-text-color has-base-font-size">Kurzer Teasertext mit maximal drei Zeilen, der Leser:innen Lust auf mehr macht.</p>
		<!-- /wp:paragraph -->

		<!-- wp:paragraph {"className":"tw-flex tw-items-center tw-gap-2 tw-font-medium","fontSize":"sm","textColor":"almost-black"} -->
		<p class="tw-flex tw-items-center tw-gap-2 tw-font-medium has-almost-black-color has-text-color has-sm-font-size">Veröffentlicht am · 12. Juni 2024<span aria-hidden="true">→</span></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
