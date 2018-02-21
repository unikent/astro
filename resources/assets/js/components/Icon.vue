<template>
<svg :class="className" :width="width" :height="height" :viewBox="glyph.viewBox">
	<use :xlink:href="glyph.symbol" />
</svg>
</template>

<script>
import * as icons from './icons';

/* global __HMR__, process */

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
			const icon = icons[this.name.replace(/[^a-z]/g, '')] || icons.unknown;

			if(__HMR__) {
				icon.symbol = icon.symbol.replace(__HMR_URL__, '');
			}

			return icon;
		}
	}
};
</script>