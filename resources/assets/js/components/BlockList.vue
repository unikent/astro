<template>
	<ul class="block-list">
		<li v-for="(block, name) in blocks">{{ name }}</li>
	</ul>
</template>

<script>
	import axios from 'axios';

	export default {

		data() {
			return {
				blocks: {}
			}
		},

		methods: {
			fetchData() {
				axios
					.get('/api/definition')
					.then((response) => {
						this.blocks = response.data;
					});
			}
		},

		created() {
			this.fetchData();
		}
	}
</script>

<style lang="scss">

</style>