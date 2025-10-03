(function (wp) {
	const { addFilter } = wp.hooks;

	if (!addFilter) {
		return;
	}

	const extendAllowedBlocks = (settings, name) => {
		const targets = ['core/navigation', 'core/navigation-submenu'];

		if (!targets.includes(name)) {
			return settings;
		}

		const extraBlocks = ['core/group', 'core/row', 'core/columns'];
		const allowed = new Set([...(settings.allowedBlocks || []), ...extraBlocks]);

		return { ...settings, allowedBlocks: Array.from(allowed) };
	};

	addFilter(
		'blocks.registerBlockType',
		'fitness-skg/extend-navigation-allowed-blocks',
		extendAllowedBlocks
	);
})(window.wp || {});
