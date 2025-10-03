(function (wp) {
	const META_KEY = '_featured_video_id';
	const managedBlocks = new Map();

	const { registerPlugin } = wp.plugins || {};
	const { useEffect, useState } = wp.element || {};
	const { useSelect } = wp.data || {};

	if (!registerPlugin || !useEffect || !useSelect) {
		return;
	}

	function walkBlocks(blocks, callback) {
		if (!Array.isArray(blocks)) {
			return;
		}

		blocks.forEach((block) => {
			callback(block);

			if (Array.isArray(block.innerBlocks) && block.innerBlocks.length) {
				walkBlocks(block.innerBlocks, callback);
			}
		});
	}

	function getBlockElement(clientId) {
		return document.querySelector(`[data-block="${clientId}"]`);
	}

	function ensureVideoForBlock(clientId, posterUrl, videoUrl) {
		const blockElement = getBlockElement(clientId);
		if (!blockElement) {
			return;
		}

		const coverElement = blockElement.querySelector('.wp-block-cover');
		if (!coverElement) {
			return;
		}

		const imageElement = coverElement.querySelector('.wp-block-cover__image-background');
		let videoEntry = managedBlocks.get(clientId);
		let videoElement = videoEntry ? videoEntry.video : null;

		if (!videoElement || !videoElement.isConnected) {
			videoElement = document.createElement('video');
			videoElement.dataset.fitnessSkgFeaturedVideo = '1';
			videoElement.className = 'wp-block-cover__video-background intrinsic-ignore';
			videoElement.autoplay = true;
			videoElement.muted = true;
			videoElement.loop = true;
			videoElement.playsInline = true;
			videoElement.preload = 'metadata';

			const backgroundSpan = coverElement.querySelector('.wp-block-cover__background');
			if (backgroundSpan) {
				backgroundSpan.insertAdjacentElement('afterend', videoElement);
			} else {
				coverElement.insertBefore(videoElement, coverElement.firstChild);
			}
		}

		if (videoElement.getAttribute('poster') !== (posterUrl || null)) {
			if (posterUrl) {
				videoElement.setAttribute('poster', posterUrl);
			} else {
				videoElement.removeAttribute('poster');
			}
		}

		if (videoElement.getAttribute('src') !== videoUrl) {
			videoElement.setAttribute('src', videoUrl);
			videoElement.load();
		}

		if (imageElement) {
			if (!('fitnessSkgOriginalDisplay' in imageElement.dataset)) {
				imageElement.dataset.fitnessSkgOriginalDisplay = imageElement.style.display || '';
			}
			imageElement.style.display = 'none';

			const objectFit = imageElement.getAttribute('data-object-fit');
			if (objectFit) {
				videoElement.setAttribute('data-object-fit', objectFit);
				videoElement.style.objectFit = objectFit;
			} else {
				videoElement.removeAttribute('data-object-fit');
				videoElement.style.removeProperty('object-fit');
			}

			const objectPosition = imageElement.style.objectPosition;
			if (objectPosition) {
				videoElement.style.objectPosition = objectPosition;
				videoElement.style.setProperty('object-position', objectPosition, '');
			} else {
				videoElement.style.removeProperty('object-position');
			}
		}

		managedBlocks.set(clientId, { video: videoElement, image: imageElement || null });
	}

	function resetBlock(clientId) {
		const entry = managedBlocks.get(clientId);
		if (!entry) {
			return;
		}

		if (entry.video && entry.video.isConnected) {
			entry.video.remove();
		}

		if (entry.image) {
			const originalDisplay = entry.image.dataset.fitnessSkgOriginalDisplay || '';
			entry.image.style.display = originalDisplay;
		}

		managedBlocks.delete(clientId);
	}

	const FeaturedVideoCoverPreview = () => {
		const meta = useSelect(
			(select) => select('core/editor').getEditedPostAttribute('meta') || {},
			[]
		);
		const blocks = useSelect(
			(select) => select('core/block-editor').getBlocks(),
			[]
		);
		const featuredImageId = useSelect(
			(select) => select('core/editor').getEditedPostAttribute('featured_media') || 0,
			[]
		);
		const featuredImage = useSelect(
			(select) => {
				if (!featuredImageId) {
					return null;
				}
				return select('core').getMedia(featuredImageId);
			},
			[featuredImageId]
		);
		const posterUrl = featuredImage && featuredImage.source_url ? featuredImage.source_url : null;

		const rawMetaValue = meta[META_KEY];
		const parsedId = rawMetaValue ? parseInt(rawMetaValue, 10) : 0;
		const videoId = Number.isNaN(parsedId) ? 0 : parsedId;

		const [videoUrl, setVideoUrl] = useState(null);

		useEffect(() => {
			let cancelled = false;

			if (!videoId) {
				setVideoUrl(null);
				return undefined;
			}

			const fetchMedia = async () => {
				try {
					const media = await wp.data.resolveSelect('core').getMedia(videoId);
					if (!cancelled) {
						setVideoUrl(media && media.source_url ? media.source_url : null);
					}
				} catch (error) {
					if (!cancelled) {
						setVideoUrl(null);
					}
				}
			};

			fetchMedia();

			return () => {
				cancelled = true;
			};
		}, [videoId]);

		useEffect(() => {
			const activeClientIds = new Set();

			walkBlocks(blocks, (block) => {
				if (!block || block.name !== 'core/cover') {
					return;
				}

				const attrs = block.attributes || {};
				const usesFeatured = !!attrs.useFeaturedImage;
				const clientId = block.clientId;

				if (usesFeatured && videoUrl) {
					ensureVideoForBlock(clientId, posterUrl, videoUrl);
					activeClientIds.add(clientId);
				} else {
					resetBlock(clientId);
				}
			});

			Array.from(managedBlocks.keys()).forEach((clientId) => {
				if (!activeClientIds.has(clientId)) {
					resetBlock(clientId);
				}
			});
		}, [blocks, videoUrl, posterUrl]);

		useEffect(() => {
			if (!featuredImageId) {
				return undefined;
			}

			const fetchImage = async () => {
				try {
					await wp.data.resolveSelect('core').getMedia(featuredImageId);
				} catch (error) {
					// ignore; poster will remain null
				}
			};

			fetchImage();

			return undefined;
		}, [featuredImageId]);

		return null;
	};

	registerPlugin('fitness-skg-featured-video-cover-preview', {
		render: FeaturedVideoCoverPreview,
	});
})(window.wp || {});
