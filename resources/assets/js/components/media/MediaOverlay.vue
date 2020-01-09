<template>
<el-dialog title="Details" :visible.sync="visible" :class="{ 'el-dialog--large': media.type === 'image' }">
	<div v-if="media" class="columns">
		<div v-if="media.type === 'image'" class="column">
			<div class="preview-dialog__image-outer">
				<div class="preview-dialog__image-wrapper">
					<div class="preview-dialog__image-img" :style="`
						background: url('${media.url}') center center / contain no-repeat;
					`" />
				</div>
			</div>
		</div>
		<div class="column is-one-third" :class="{ 'is-one-third' : media.type === 'image' }">
			<div class="preview-dialog__details-wrapper">
				<div v-for="(value, key) in getAttributes(media)" v-if="value">
					<h3>{{ prettyName(key) }}</h3>
					<p v-html="transformAttr(key, value)" />
				</div>
				<div v-if="media.colours">
					<h3>Colour palette</h3>
					<div class="preview-dialog__details-colour-wrapper">
						<div
							v-for="rgb in media.colours"
							class="preview-dialog__details-colours"
							:style="`background-color: rgb(${rgb.join(',')})`"
						/>
					</div>
				</div>
			</div>
		</div>
	</div>
</el-dialog>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import _ from 'lodash';
import ColorThief from 'color-thief-standalone';

import mediaFormatters from 'mixins/mediaFormatters';

/* global Image */

export default {

	name: 'media-overlay',

	props: {
		size: {
			default: 'large'
		}
	},

	mixins: [mediaFormatters],

	watch: {
		visible(show) {
			if(show) {
				if(this.mediaItem.type === 'image') {
					const img = new Image();
					img.crossOrigin = 'Anonymous';
					img.addEventListener('load', () => {
						const colorThief = new ColorThief();
						this.colourPalette = colorThief.getPalette(img, 6)
					});

					img.src = this.mediaItem.url;
				}
				else {
					this.colourPalette = null;
				}
			}
			else {
				this.colourPalette = null;
			}
		}
	},

	created() {
		this.colours = null;
		this.prettyNames = {
			'url'         : 'URL',
			'filename'    : 'File name',
			'filesize'    : 'File size',
			'type'        : 'File type',
			'height'      : 'Height',
			'width'       : 'Width',
			'aspect_ratio': 'Aspect ratio'
		};
	},

	data() {
		return {
			colourPalette: null
		}
	},

	computed: {
		...mapState({
			mediaOverlayVisible: state => state.media.mediaOverlayVisible,
			mediaItem: state => state.media.mediaItem
		}),

		visible: {
			get() {
				return this.mediaOverlayVisible;
			},
			set(show) {
				if(!show) {
					this.hideMediaOverlay();
				}
			}
		},

		media: {
			get() {
				return { ...this.mediaItem, colours: this.colourPalette };
			},
			set() {}
		}
	},

	methods: {
		...mapActions([
			'hideMediaOverlay'
		]),

		getAttributes(media) {
			return _.pick(media, [
				'url', 'type', 'filename', 'filesize',
				'height', 'width', 'aspect_ratio'
			]);
		},

		prettyName(attr) {
			return this.prettyNames[attr] ? this.prettyNames[attr] : attr;
		},

		transformAttr(name, attr) {
			let rounded;

			switch(name) {
				case 'filesize':
					return this.formatBytes(attr);
				case 'aspect_ratio':
					rounded = Math.round(attr * 10) / 10;

					return (
						this.formatAspectRatioFromDecimal(rounded, 1000) +
						(rounded !== attr ? ' approx.' : '')
					);
			}

			return attr;
		}

	}
};
</script>
