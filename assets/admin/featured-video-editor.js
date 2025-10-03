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
			const blockEditorDispatch = wp.data.dispatch('core/block-editor');
			if (!blockEditorDispatch) {
				return;
			}

			const applyUpdate = (clientId, attrs) => {
				const markNonPersistent = blockEditorDispatch.__unstableMarkNextChangeAsNotPersistent;
				if (typeof markNonPersistent === 'function') {
					markNonPersistent(() => blockEditorDispatch.updateBlockAttributes(clientId, attrs));
					return;
				}

				blockEditorDispatch.updateBlockAttributes(clientId, attrs);
			};

			walkBlocks(blocks, (block) => {
				if (!block || block.name !== 'core/cover') {
					return;
				}

				const attrs = block.attributes || {};
				const clientId = block.clientId;
				const wasManaged = managedBlocks.has(clientId);
				const initiallyUsesFeatured = !!attrs.useFeaturedImage;
				const shouldHandle = initiallyUsesFeatured || wasManaged;

				if (!shouldHandle) {
					return;
				}

				if (initiallyUsesFeatured) {
					managedBlocks.set(clientId, true);
				}

				if (videoId && videoUrl) {
					const needsUpdate =
						attrs.backgroundType !== 'video' ||
						attrs.videoID !== videoId ||
						attrs.videoURL !== videoUrl ||
						attrs.useFeaturedImage !== false ||
						(posterUrl && attrs.url !== posterUrl) ||
						(!posterUrl && attrs.url) ||
						(featuredImageId && attrs.id !== featuredImageId) ||
						(!featuredImageId && attrs.id);

					if (needsUpdate) {
						const nextAttrs = {
							backgroundType: 'video',
							videoID: videoId,
							videoURL: videoUrl,
							useFeaturedImage: false,
						};

						if (posterUrl) {
							nextAttrs.url = posterUrl;
						} else if (attrs.url) {
							nextAttrs.url = undefined;
						}

						if (featuredImageId) {
							nextAttrs.id = featuredImageId;
						} else if (attrs.id) {
							nextAttrs.id = undefined;
						}

						applyUpdate(clientId, nextAttrs);
					}
				} else {
					const needsReset =
						attrs.useFeaturedImage !== true ||
						attrs.backgroundType === 'video' ||
						attrs.videoID ||
						attrs.videoURL;

					if (needsReset) {
						applyUpdate(clientId, {
							backgroundType: 'image',
							useFeaturedImage: true,
							videoID: undefined,
							videoURL: undefined,
							url: undefined,
							id: undefined,
						});
					}

					managedBlocks.delete(clientId);
				}
			});
		}, [blocks, videoId, videoUrl, featuredImageId, posterUrl]);

		return null;
	};

	registerPlugin('fitness-skg-featured-video-cover-preview', {
		render: FeaturedVideoCoverPreview,
	});
})(window.wp || {});
