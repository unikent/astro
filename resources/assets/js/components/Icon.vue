<template>
<svg :class="className" :width="width" :height="height" :viewBox="glyph.viewBox">
	<use :xlink:href="glyph.symbol" />
</svg>
</template>

<script>
export default {
	props: {
		glyph: {},
		width: {
			default: 17
		},
		height: {
			default: 17
		},
		className: {
			default: 'icon'
		}
	}
};
</script>