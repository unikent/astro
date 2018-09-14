import { mapGetters, mapState } from 'vuex';

import baseFieldMixin from 'mixins/baseFieldMixin';
import blockErrorsMixin from 'mixins/blockErrorsMixin';

export default {

	name: 'block-field',

	mixins: [baseFieldMixin, blockErrorsMixin],

	computed: {
		...mapGetters([
			'getCurrentFieldValue',
			'currentSectionIndex',
			'currentDefinition'
		]),

		...mapState({
			currentBlockIndex: state => state.contenteditor.currentBlockIndex,
			currentRegionName: state => state.contenteditor.currentRegionName,
		})
	},

	methods: {
		getFieldValue(path) {
			const value = this.getCurrentFieldValue(path);
			return value !== void 0 ? value : (this.default || null);
		},

		updateFieldValue(path, value) {
			this.$store.commit('updateFieldValue', {
				name: path,
				value: value,
				index: this.currentBlockIndex,
				region: this.currentRegionName,
				section: this.currentSectionIndex
			});

			this.$bus.$emit('block:validate');
		}
	}

};
