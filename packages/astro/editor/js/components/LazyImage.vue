<template>
	<div v-if="bg && !template" :style="bgStyle" />
	<img v-else-if="!template" :src="imageSrc" />
	<component v-else :is="template" />
</template>

<script>
/* global Image, console */

export default {

	props: {
		src: {
			type: String,
			required: true
		},
		smallSrc: {
			required: true
		},
		template: {
			type: String
		},
		aspect: {
			type: Number
		},
		bg: {
			type: Boolean
		},
		'on-start': {
			type: Function,
			default: () => {}
		},
		'on-load': {
			type: Function,
			default: () => {}
		}
	},

	data() {
		return {
			imageSrc: this.smallSrc
		}
	},

	computed: {
		bgStyle() {
			return {
				backgroundSize: 'cover',
				paddingTop: '100%',
				backgroundPosition: '50%',
				backgroundImage: `url('${this.imageSrc}')`
			};
		}
	},

	watch: {
		src() {
			this.imageSrc = this.smallSrc;
			this.loadImage();
		}
	},

	mounted() {
		this.loadImage();
	},

	methods: {
		loadImage() {
			this.onStart();

			let img = new Image();

			img.addEventListener('load', () => {
				this.imageSrc = this.src;
				this.onLoad();
			});

			img.addEventListener('error', () => {
				console.warn(`Oops! We couldnt load "${img.src}".`);
			});

			img.src = this.src;
		}
	}

};
</script>