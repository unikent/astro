<template>
	<el-input v-model="content"></el-input>
</template>

<script>
	import { mapActions } from 'vuex';

	export default {

		props: ['name'],

		computed: {
			content: {
				get () {
					return this.$store.getters.getCurrentFieldValue(this.name);
				},
				set(value) {
					this.updateValue({ name: this.name, value });
				}
			}
		},

		methods: {
			...mapActions([
				'updateValue'
			])
		}

	}
</script>