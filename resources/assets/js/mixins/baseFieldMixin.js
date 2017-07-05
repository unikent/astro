import { mapMutations } from 'vuex';

export default {

	props: ['field', 'name'],

	data() {
		return this.field;
	},

	computed: {
		value: {
			get() {
				const value = this.$store.getters.getCurrentFieldValue(this.name);
				return value !== void 0 ? value : this.default;
			},
			set(value) {
				this.updateFieldValue({
					name: this.name,
					value: this.transformValue(value)
				});
			}
		}
	},

	methods: {
		...mapMutations([
			'updateFieldValue'
		]),

		// Here so we can override this at some point
		transformValue(value) {
			return value;
		}
	}

};
