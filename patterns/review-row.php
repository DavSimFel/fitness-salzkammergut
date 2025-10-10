<?php
/**
 * Title: Review row
 * Slug: fitness-salzkammergut/review-row
 * Categories: testimonials
 * Description: Drei Testimonials im flexiblen Row-Layout mit Tailwind-Styling.
 */
?>
<!-- wp:group {"className":"tw-flex tw-flex-col tw-gap-8"} -->
<div class="wp-block-group tw-flex tw-flex-col tw-gap-8">
	<!-- wp:heading {"level":2,"className":"tw-font-semibold","fontSize":"3xl","textColor":"soft-white"} -->
	<h2 class="tw-font-semibold has-soft-white-color has-text-color has-3xl-font-size">Das sagen unsere Mitglieder</h2>
	<!-- /wp:heading -->

	<!-- wp:group {"tagName":"section","className":"tw-review-carousel tw-rounded-[2rem] tw-bg-[#1C1F24]/60 tw-p-6 tw-shadow-[0_40px_80px_-60px_rgba(12,16,26,0.85)] tw-backdrop-blur-xl","layout":{"type":"constrained","justifyContent":"center"}} -->
	<section class="wp-block-group tw-review-carousel tw-rounded-[2rem] tw-bg-[#1C1F24]/60 tw-p-6 tw-shadow-[0_40px_80px_-60px_rgba(12,16,26,0.85)] tw-backdrop-blur-xl" data-review-carousel role="region" aria-label="Stimmen unserer Mitglieder" tabindex="0">
		<!-- wp:group {"className":"tw-review-carousel__track tw-gap-6","layout":{"type":"constrained"}} -->
		<div class="wp-block-group tw-review-carousel__track tw-gap-6" data-review-carousel-track>
			<!-- wp:fitness/review-card {"testimonialId":0} /-->

			<!-- wp:fitness/review-card {"testimonialId":0} /-->

			<!-- wp:fitness/review-card {"testimonialId":0} /-->
		</div>
		<!-- /wp:group -->

		<!-- wp:html -->
		<button type="button" class="tw-review-carousel__nav tw-review-carousel__nav--prev" data-review-carousel-prev aria-label="Vorherige Bewertung">
			<span aria-hidden="true">‹</span>
			<span class="tw-sr-only">Vorherige Bewertung anzeigen</span>
		</button>
		<!-- /wp:html -->

		<!-- wp:html -->
		<button type="button" class="tw-review-carousel__nav tw-review-carousel__nav--next" data-review-carousel-next aria-label="Nächste Bewertung">
			<span aria-hidden="true">›</span>
			<span class="tw-sr-only">Nächste Bewertung anzeigen</span>
		</button>
		<!-- /wp:html -->
	</section>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
