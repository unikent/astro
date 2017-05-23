<template>
	<div>
		<h4>Link text</h4>
		<el-input v-model="text"></el-input>
		<h4>Link URL</h4>
		<el-input v-model="url"></el-input>
	</div>
</template>

<script>
	import { mapMutations } from 'vuex';

	export default {

		name: 'link-field',

		props: ['name'],

		computed: {
			text: {
				get() {
					return this.getField(this.name, 'text');
				},
				set(value) {
					const url = this.getField(this.name, 'url');

					this.updateFieldValue({
						name: this.name,
						value: {
							text: value,
							url
						}
					});
				}
			},

			url: {
				get() {
					return this.getField(this.name, 'text');
				},
				set(value) {
					const text = this.getField(this.name, 'text');

					this.updateFieldValue({
						name: this.name,
						value: {
							text,
							url: value
						}
					});
				}
			}
		},

		methods: {
			...mapMutations([
				'updateFieldValue'
			]),

			getField(name, key = false) {
				const field = this.$store.getters.getCurrentFieldValue(name);

				if(key) {
					return field && field[key] ? field[key] : '';
				}

				return this.$store.getters.getCurrentFieldValue(name);
			}
		}

	}
</script>