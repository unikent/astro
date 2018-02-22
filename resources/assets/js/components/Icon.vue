<template>
	<svg :class="className" :width="width" :height="height" :viewBox="glyph.viewBox">
		<use :xlink:href="glyph.symbol" />
	</svg>
</template>

<script>
import * as icons from './icons';

// These HMR globals are set by webpack
/* global __HMR__, __HMR_URL__ */

export default {
	props: {
		name: {
			type: String,
			default: 'unknown'
		},

		width: {
			type: Number,
			default: 17
		},

		height: {
			type: Number,
			default: 17
		},

		className: {
			type: String,
			default: 'icon'
		}
	},

	computed: {
		glyph() {
			const icon = icons[this.name.replace(/[^a-z]/g, '')] || icons.unknown;

			// In production mode the global "__HMR__" is set to false,
			// and after minification this if condition is removed entirely.
			if(__HMR__) {
				// We can't load SVGs from a different domain, port or protocol,
				// so when HMR is enabled we change the icon URL to the main domain.
				icon.symbol = icon.symbol.replace(__HMR_URL__, '');
			}

			return icon;
		}
	}
};
</script>