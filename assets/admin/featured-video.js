jQuery(function ($) {
	const setBtn = $('#fitness_skg_featured_video_set');
	const removeBtn = $('#fitness_skg_featured_video_remove');
	const input = $('#fitness_skg_featured_video_id');
	const preview = $('#fitness_skg_featured_video_preview');

	let frame;

	function updatePreview(attachment) {
		if (attachment && attachment.id) {
			input.val(attachment.id);
			removeBtn.show();

			const video = preview.find('video')[0];
			if (video) {
				const source = $(video).find('source');
				source.attr('src', attachment.url);
				video.load();
			}

			preview.show();
		} else {
			input.val('');
			removeBtn.hide();
			preview.hide();
		}
	}

	setBtn.on('click', function (event) {
		event.preventDefault();

		if (frame) {
			frame.open();
			return;
		}

		frame = wp.media({
			title: 'Set featured video',
			button: { text: 'Use this video' },
			library: { type: 'video' },
			multiple: false,
		});

		frame.on('select', function () {
			const attachment = frame.state().get('selection').first().toJSON();
			updatePreview(attachment);
		});

		frame.open();
	});

	removeBtn.on('click', function (event) {
		event.preventDefault();
		updatePreview(null);
	});
});
