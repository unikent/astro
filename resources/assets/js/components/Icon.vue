<template>
<svg :class="className" :width="width" :height="height" :viewBox="glyph.viewBox">
	<use :xlink:href="glyph.symbol" />
</svg>
</template>

<script>
import astro from 'IconPath/unknown.svg';

export default {
	props: {
		glyph: {
			default: () => astro
		},

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