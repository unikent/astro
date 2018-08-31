/**
 * the block editing form for editing a single block within a page
 * displayed in the sidebar
 *
 * listens to validation events from block-form and updates validation
 * within the the global vuex state
 */
import { mapState, mapMutations, mapGetters } from 'vuex';

import EditOptions from 'components/EditOptions';

export default {

	name: 'edit-block',

	extends: EditOptions,

	computed: {
		...mapGetters([
			'currentBlock',
			'currentDefinition'
		]),

		...mapState({
			currentRegion: state => state.contenteditor.currentRegionName,
			currentSectionIndex: state => state.contenteditor.currentSectionIndex,
			currentIndex: state => state.contenteditor.currentBlockIndex,
			blockId: state => state.contenteditor.currentBlockId
		}),

		loading() {
			return false;
		},

		definition() {
			return this.currentDefinition;
		},

		currentItem() {
			return this.currentBlock;
		},

		errors() {
			return this.$store.state.errors.blocks;
		},

		identifier() {
			return `${this.currentRegion}_${this.currentSectionIndex}_${this.currentIndex}`;
		}
	},

	watch: {
		currentBlock(val, oldVal) {
			// if block is removed hide sidebar
			if(val === void 0) {
				this.setBlock();
			}

			// if block changes scroll to top
			if(val && oldVal && val.id !== oldVal.id) {
				this.$refs['options-list'].scrollTop = 0;
			}
		},
	},

	methods: {
		...mapMutations([
			'setBlock'
		]),

		getErrors(fieldPath) {
			const blockId = this.currentItem.id

			if(
				this.errors[blockId] &&
				this.errors[blockId].errors[fieldPath] &&
				this.errors[blockId].errors[fieldPath].length
			) {
				return this.errors[blockId].errors[fieldPath].join(', ');
			}
		}
	}
};
