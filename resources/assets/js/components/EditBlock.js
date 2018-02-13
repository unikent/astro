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

	created() {
		eventBus.$on('block:setValidation', this.setValidation);
		eventBus.$on('block:setValidation', this.setValidation);
	},

	computed: {
		...mapGetters([
			'currentBlock',
			'currentDefinition'
		]),

		...mapState({
			currentRegion: state => state.contenteditor.currentRegionName,
			currentSectionName: state => state.contenteditor.currentSectionName,
			currentIndex: state => state.contenteditor.currentBlockIndex
		}),

		definition() {
			return this.currentDefinition;
		},

		currentItem() {
			return this.currentBlock && this.currentBlock.fields;
		},

		identifier() {
			return `${this.currentRegion}_${this.currentSectionName}_${this.currentIndex}`;
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
			'setBlock',
			'addBlockValidationIssue',
			'deleteBlockValidationIssue'
		]),

		/**
		 * toggles the validation of this current block in the vuex state
		 * @param {bool} status
		 */
		setValidation(status) {
			if (this.currentBlock) {
				if (status) {
					this.deleteBlockValidationIssue(this.currentBlock.id);
				}
				else {
					this.addBlockValidationIssue(this.currentBlock.id);
				}
			}
		}
	}
};
