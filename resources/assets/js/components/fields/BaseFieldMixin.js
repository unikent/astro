import { mapActions } from 'vuex';

export default {

	props: ['field'],

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
				this.updateValue({ name: this.name, value });
			}
		}
	},

	methods: {
		...mapActions([
			'updateValue'
		])
	}

};
