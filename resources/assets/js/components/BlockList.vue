<template>
	<ul class="block-list">
		<li v-for="(block, name) in blocks">{{ name }}</li>
	</ul>
</template>

<script>
	import api from '../libs/api';

	export default {

		data() {
			return {
				blocks: {}
			}
		},

		methods: {
			fetchData() {
				api
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