<template>
<svg :class="className" :width="width" :height="height" :viewBox="glyph.viewBox">
	<use :xlink:href="glyph.symbol" />
</svg>
</template>

<script>
import * as icons from './icons';

export default {
	props: {
		name: {
			default: 'unknown'
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
	},

	computed: {
		glyph() {
			return icons[this.name.replace(/[^a-z]/g, '')] || icons.unknown;
		}
	}
};
</script>