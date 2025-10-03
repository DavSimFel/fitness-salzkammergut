<?php
/**
 * Title: header
 * Slug: fitness-salzkammergut/header
 * Inserter: no
 */
?>
<!-- wp:group {"tagName":"header","className":"tw-site-hero js-site-hero"} -->
<header class="wp-block-group tw-site-hero js-site-hero">
	<!-- wp:group {"className":"tw-site-nav js-site-nav"} -->
	<div class="wp-block-group tw-site-nav js-site-nav">
		<!-- wp:group {"className":"tw-site-nav__inner tw-container-gutter tw-flex tw-items-center tw-justify-between tw-gap-4","layout":{"type":"flex","justifyContent":"space-between","flexWrap":"wrap","verticalAlignment":"center"},"style":{"spacing":{"padding":{"top":"1.25rem","bottom":"1.25rem"}}}} -->
		<div class="wp-block-group tw-site-nav__inner tw-container-gutter tw-flex tw-items-center tw-justify-between tw-gap-4" style="padding-top:1.25rem;padding-bottom:1.25rem">
			<!-- wp:group {"className":"tw-site-nav__brands tw-flex tw-items-center tw-gap-4","layout":{"type":"flex","flexWrap":"wrap","verticalAlignment":"center"}} -->
			<div class="wp-block-group tw-site-nav__brands tw-flex tw-items-center tw-gap-4">
				<!-- wp:image {"lightbox":{"enabled":false},"width":"auto","height":"24px","sizeSlug":"full","linkDestination":"custom"} -->
				<figure class="wp-block-image size-full is-resized"><a href="https://fitness-salzkammergut.at/studio/vivo-bad-goisern/"><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/Dachmarke_OnLight_vivo.svg" alt="VIVO Fitness" style="width:auto;height:24px"/></a></figure>
				<!-- /wp:image -->

				<!-- wp:image {"width":"auto","height":"36px","sizeSlug":"full","linkDestination":"none"} -->
				<figure class="wp-block-image size-full is-resized"><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/Dachmarke_OnLight_dot.svg" alt="Fitness Salzkammergut" style="width:auto;height:36px"/></figure>
				<!-- /wp:image -->

				<!-- wp:image {"lightbox":{"enabled":false},"width":"auto","height":"24px","sizeSlug":"full","linkDestination":"custom"} -->
				<figure class="wp-block-image size-full is-resized"><a href="https://fitness-salzkammergut.at/studio/excitingfit-bad-ischl/"><img src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/Dachmarke_OnLight_excitingfit.svg" alt="Exciting Fit" style="width:auto;height:24px"/></a></figure>
				<!-- /wp:image -->
			</div>
			<!-- /wp:group -->

			<!-- wp:navigation {"overlayMenu":"mobile","layout":{"type":"flex","justifyContent":"center","flexWrap":"wrap"}} /-->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->

	<!-- wp:cover {"useFeaturedImage":true,"dimRatio":30,"overlayColor":"almost-black","focalPoint":{"x":0.5,"y":0.5},"minHeight":100,"minHeightUnit":"vh","isDark":true,"className":"tw-site-hero__cover"} -->
	<div class="wp-block-cover tw-site-hero__cover is-dark" style="min-height:100vh"><span aria-hidden="true" class="wp-block-cover__background has-almost-black-background-color has-background-dim-30 has-background-dim"></span><div class="wp-block-cover__inner-container">
		<!-- wp:group {"className":"tw-site-hero__content tw-container-gutter tw-flex tw-flex-col tw-gap-6","layout":{"type":"flex","orientation":"vertical","justifyContent":"center"},"style":{"spacing":{"padding":{"top":"9rem","bottom":"6rem"}}}} -->
		<div class="wp-block-group tw-site-hero__content tw-container-gutter tw-flex tw-flex-col tw-gap-6" style="padding-top:9rem;padding-bottom:6rem">
			<!-- wp:group {"className":"tw-flex tw-flex-col tw-gap-5 tw-max-w-3xl"} -->
			<div class="wp-block-group tw-flex tw-flex-col tw-gap-5 tw-max-w-3xl">
				<!-- wp:heading {"level":1,"className":"tw-font-semibold","textColor":"soft-white","fontSize":"4xl"} -->
				<h1 class="tw-font-semibold has-soft-white-color has-text-color has-4xl-font-size">Dein Training im Salzkammergut</h1>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"className":"tw-leading-relaxed","textColor":"soft-white","fontSize":"lg"} -->
				<p class="tw-leading-relaxed has-soft-white-color has-text-color has-lg-font-size">Passe Headline, Text und Buttons an, um aktuelle Kampagnen oder Standort-Highlights zu kommunizieren.</p>
				<!-- /wp:paragraph -->

				<!-- wp:buttons {"className":"tw-flex tw-flex-wrap tw-gap-4"} -->
				<div class="wp-block-buttons tw-flex tw-flex-wrap tw-gap-4">
					<!-- wp:button {"className":"tw-btn tw-btn--primary"} -->
					<div class="wp-block-button tw-btn tw-btn--primary"><a class="wp-block-button__link" href="/probetraining/">Probetraining buchen</a></div>
					<!-- /wp:button -->

					<!-- wp:button {"className":"tw-btn tw-btn--secondary"} -->
					<div class="wp-block-button tw-btn tw-btn--secondary"><a class="wp-block-button__link" href="/studios/">Standorte entdecken</a></div>
					<!-- /wp:button -->
				</div>
				<!-- /wp:buttons -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
	</div></div>
	<!-- /wp:cover -->
</header>
<!-- /wp:group -->

<!-- wp:html -->
<style>
.tw-site-hero {
	position: relative;
}

.tw-site-nav {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	z-index: 1000;
	background-color: transparent;
	transition: background-color 0.35s ease, box-shadow 0.35s ease;
}

.tw-site-nav.is-scrolled {
	background-color: var(--wp--preset--color--almost-black, #1b1a18);
	box-shadow: 0 16px 42px -18px rgba(0, 0, 0, 0.5);
	backdrop-filter: blur(12px);
}

.tw-site-nav__inner {
	max-width: 1280px;
	margin-left: auto;
	margin-right: auto;
}

.tw-site-hero__cover {
	min-height: 100vh;
}

.tw-site-hero__cover .wp-block-cover__inner-container {
	min-height: inherit;
	display: flex;
	align-items: flex-end;
}

.tw-site-hero__cover .wp-block-cover__video-background,
.tw-site-hero__cover .wp-block-cover__image-background {
	object-fit: cover;
}

body.has-fixed-header {
	padding-top: var(--fitness-skg-header-offset, 0px);
}

@media (max-width: 782px) {
	.tw-site-nav__inner {
		padding-left: 1.25rem !important;
		padding-right: 1.25rem !important;
		gap: 1rem;
	}
}
</style>
<script>
(function () {
	if (window.fitnessSkgHeaderInit) {
		return;
	}

	window.fitnessSkgHeaderInit = true;

	const nav = document.querySelector('.js-site-nav');
	const hero = document.querySelector('.tw-site-hero__cover');

	if (!nav || !hero) {
		return;
	}

	const getThreshold = () => {
		const heroHeight = hero.offsetHeight || 0;
		const navHeight = nav.offsetHeight || 0;
		return Math.max(heroHeight - navHeight, 0);
	};

	let threshold = getThreshold();

	const setBodyOffset = (active) => {
		if (!document.body) {
			return;
		}

		if (active) {
			document.body.classList.add('has-fixed-header');
			document.body.style.setProperty('--fitness-skg-header-offset', `${nav.offsetHeight || 0}px`);
		} else {
			document.body.classList.remove('has-fixed-header');
			document.body.style.removeProperty('--fitness-skg-header-offset');
		}
	};

	const updateNavState = () => {
		const shouldStick = window.scrollY >= threshold;
		nav.classList.toggle('is-scrolled', shouldStick);
		setBodyOffset(shouldStick);
	};

	const recalc = () => {
		threshold = getThreshold();
		updateNavState();
	};

	window.addEventListener('scroll', updateNavState, { passive: true });
	window.addEventListener('resize', recalc);
	window.addEventListener('load', recalc);
	document.addEventListener('DOMContentLoaded', recalc);
	recalc();
})();
</script>
<!-- /wp:html -->
