import { mapActions, mapGetters, mapMutations } from 'vuex';

export default {

	props: ['field', 'path', 'errors', 'currentDefinition'],

	data() {
		return this.field;
	},

	computed: {
		...mapGetters([
			'getCurrentFieldValue'
		]),

		value: {
			get() {
				const value = this.getCurrentFieldValue(this.path);
				return value !== void 0 ? value : this.default;
			},
			set(value) {
				this.updateFieldValue({
					name: this.path,
					value: this.transformValue(value)
				});
			}
		}
	},

	methods: {
		...mapActions([
			'updateFieldValue'
		]),

		// Here so we can override this at some point
		transformValue(value) {
			return value;
		}
	}

};
