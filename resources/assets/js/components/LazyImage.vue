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
			type: String,
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
			this.loadImage();
		}
	},

	mounted() {
		this.loadImage();
	},

	methods: {
		loadImage() {
			let img = new Image();

			img.addEventListener('load', () => {
				this.imageSrc = this.src;
			});

			img.addEventListener('error', () => {
				console.warn(`Oops! We couldnt load the "${img.src}" image.`);
			});

			img.src = this.src;
		}
	}

};
</script>