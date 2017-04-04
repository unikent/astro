<template>
	<el-select v-model="value" :placeholder="options.placeholder">
		<el-option
			v-for="option in options"
			:label="option.label"
			:value="option.value"
		>
		</el-option>
	</el-select>
</template>

<script>
	import { mapActions } from 'vuex';

	export default {

		name: 'SelectField',

		props: ['field'],

		data() {
			return this.field;
		},

		computed: {
			value: {
				get() {
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