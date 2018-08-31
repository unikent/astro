import { mapGetters, mapState } from 'vuex';

import baseFieldMixin from 'mixins/baseFieldMixin';

export default {

	name: 'block-field',

	mixins: [baseFieldMixin],

	computed: {
		...mapGetters([
			'getCurrentFieldValue',
			'currentSectionIndex',
			'currentDefinition'
		]),

		...mapState({
			currentBlockId: state => state.contenteditor.currentBlockId,
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

			this.$bus.$emit('global:validate');
		},

		getError(fieldPath) {
			const
				blockErrors = this.$store.state.errors.blocks,
				blockId = this.currentBlockId;

			if(
				blockErrors[blockId] &&
				blockErrors[blockId].errors[fieldPath] &&
				blockErrors[blockId].errors[fieldPath].length
			) {
				return blockErrors[blockId].errors[fieldPath].join(', ');
			}
			return null;
		}
	}

};
