<style>
	.bg-img {
		background-size: cover;
		padding-top: 100%;
		background-position: 50%;
	}
</style>

<template>
	<div v-if="bg" class="bg-img" :style="`background-image: url('${imageSrc}');`" />
	<img v-else :src="imageSrc" />
</template>

<script>
export default {

	props: [
		'src',
		'smallSrc',
		'template',
		'aspect',
		'bg'
	],

	data() {
		return {
			imageSrc: this.smallSrc
		}
	},

	mounted() {
		console.log(this.bg ? true : false);
		let img = new Image();

		img.addEventListener('load', (e) => {
			this.imageSrc = this.src;
		});

		img.addEventListener('error', (e) => {
			console.warn(`Oops! We couldnt load the "${img.src}" image.`);
			img.src = '/unavailable.png';
			img.onload();
		});

		img.src = this.src;
	}

};
</script>