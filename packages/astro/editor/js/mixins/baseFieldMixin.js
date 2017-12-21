import { mapGetters, mapState } from 'vuex';

export default {

	props: ['field', 'path', 'errors', 'currentDefinition'],

	data() {
		return this.field;
	},

	computed: {
		...mapGetters([
			'getCurrentFieldValue'
		]),
		...mapState({
			currentBlockIndex: state => state.contenteditor.currentBlockIndex,
			currentRegionName: state => state.contenteditor.currentRegionName,
		}),

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
		...mapGetters([
			'currentSectionIndex'
		]),

		updateFieldValue({ name, value }) {
			this.$store.commit('updateFieldValue', {
				name: name,
				value: value,
				index: this.currentBlockIndex,
				region: this.currentRegionName,
				section: this.currentSectionIndex()
			})
		},

		// Here so we can override this at some point
		transformValue(value) {
			return value;
		}
	}

};
