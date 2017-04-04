<template>
	<div>
		<h4>Link text</h4>
		<el-input v-model="text"></el-input>
		<h4>Link URL</h4>
		<el-input v-model="url"></el-input>
	</div>
</template>

<script>
	import { mapActions } from 'vuex';

	export default {

		name: 'LinkField',

		props: ['name'],

		computed: {
			text: {
				get () {
					return this.$store.getters.getCurrentFieldValue(this.name)['text'];
				},
				set(value) {
					this.updateValue({
						name: this.name,
						value: {
							text: value,
							url: this.url
						}
					});
				}
			},

			url: {
				get () {
					return this.$store.getters.getCurrentFieldValue(this.name)['url'];
				},
				set(value) {
					this.updateValue({
						name: this.name,
						value: {
							text: this.text,
							url: value
						}
					});
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