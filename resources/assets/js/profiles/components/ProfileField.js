import baseFieldMixin from 'mixins/baseFieldMixin';

export default {

	mixins: [baseFieldMixin],

	methods: {
		getFieldValue(path) {
			return this.$store.getters['profile/getFieldValue']({
				name: path,
				fallback: this.default || null
			});
		},

		updateFieldValue(path, value) {
			this.$store.commit('profile/updateFieldValue', {
				name: path,
				value: value
			})
		}
	}

};
