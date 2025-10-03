(function (wp) {
	const META_KEY = '_featured_video_id';

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
				if (!attrs.useFeaturedImage) {
					return;
				}

				if (videoId && videoUrl) {
					if (
						attrs.backgroundType !== 'video' ||
						attrs.videoID !== videoId ||
						attrs.videoURL !== videoUrl
					) {
						applyUpdate(block.clientId, {
							backgroundType: 'video',
							videoID: videoId,
							videoURL: videoUrl,
						});
					}
				} else if (!videoId) {
					const hasPreview =
						attrs.backgroundType === 'video' || attrs.videoID || attrs.videoURL;
					if (hasPreview) {
						applyUpdate(block.clientId, {
							backgroundType: 'image',
							videoID: undefined,
							videoURL: undefined,
						});
					}
				}
			});
		}, [blocks, videoId, videoUrl]);

		return null;
	};

	registerPlugin('fitness-skg-featured-video-cover-preview', {
		render: FeaturedVideoCoverPreview,
	});
})(window.wp || {});
