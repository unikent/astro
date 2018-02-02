<template>
	<iframe ref="hidden-frame" :style="styles" />
</template>

<script>
/*
 * A shim to fire an event on resize, even when scrollbars are added/removed.
 */
export default {

	name: 'scrollbar-resize-shim',

	props: {
		onResize: {
			default: () => {}
		}
	},

	created() {
		this.styles = {
			height: 0,
			margin: 0,
			padding: 0,
			overflow: 'hidden',
			borderWidth: 0,
			position: 'absolute',
			backgroundColor: 'transparent',
			width: '100%'
		};
	},

	mounted() {
		this.$refs['hidden-frame'].contentWindow.addEventListener('resize', this.onResize, false);
	},

	beforeDestroy() {
		if (this.$refs['hidden-frame'].contentWindow) {
			this.$refs['hidden-frame'].contentWindow.removeEventListener('resize', this.onResize);
		}
	}
};
</script>