<template>
<el-dialog
	:title="replaceBlocks ? 'Swap block' : 'Add block'"
	top="5%"
	:visible.sync="visible"
>
	<el-alert
		v-if="replaceBlocks"
		title="Warning"
		description="Replacing a block with another one means you will permanently lose your changes to the original block."
		type="warning"
		:closable="false"
		show-icon
	></el-alert>
	<picker-list
			:selectedOptions="selected"
			:options="availableBlocks"
			:maxSelectableOptions="maxSelectableBlocks"
	/>

	<span slot="footer" class="dialog-footer">
		<el-button @click="cancel">Cancel</el-button>
		<el-button v-if="replaceBlocks" type="primary" @click="addBlocks">Swap</el-button>
		<el-button v-else type="primary" @click="addBlocks">Add</el-button>
	</span>
</el-dialog>
</template>

<script>
/**
 * Modal which allows the user to select one or more blocks to add to a page section.
 */

import { mapState, mapMutations } from 'vuex';
import Vue from 'vue';
import { uuid } from 'classes/helpers';
import PickerList from 'components/PickerList';

export default {
	name: 'block-picker',

	components: {
		PickerList
	},

	data() {
		return {
			selected: []
		};
	},

	computed: {
		...mapState({
			blockPicker: state => state.blockPicker,
			allowedBlocks: state => state.blockPicker.allowedBlocks,
			deprecatedBlocks: state => state.blockPicker.deprecatedBlocks,
			maxSelectableBlocks: state => state.blockPicker.maxSelectableBlocks,
			replaceBlocks: state => state.blockPicker.replaceBlocks,
			allBlocks: state => state.definition.blockDefinitions,
			blocks: state => state.page.pageData.blocks
		}),

		/**
		 * Get the blocks currently available to be displayed in the block picker.
		 * @returns {Array}
		 * @todo - what is happening with the 'v1' here?
		 */
		availableBlocks() {
			let blocks = {};
			if(this.allowedBlocks) {
				for(let i in this.allowedBlocks) {
					let blockId = this.allowedBlocks[i];
					if (this.deprecatedBlocks.indexOf(blockId)) {
						if (this.allBlocks[blockId]) {
							blocks[blockId] = this.allBlocks[blockId];
						}
						else if(this.allBlocks[blockId + '-v1']) {
							blocks[blockId + '-v1'] = this.allBlocks[blockId + '-v1'];
						}
					}
				}
			}


			return blocks;
		},

		visible: {
			get() {
				return this.blockPicker.visible;
			},
			set(value) {
				if(value) {
//					this.showBlockPicker();
				}
				else {
					this.hideBlockPicker();
				}
			}
		}
	},

	methods: {
		...mapMutations([
			'showBlockPicker',
			'hideBlockPicker',
			'addBlock',
			'deleteBlockValidationIssue'
		]),

		addBlocks() {
			this.selected.forEach((blockKey, i) => {
				const { name, version } = this.availableBlocks[blockKey];
				this.addThisBlockType({
					name,
					version,
					index: this.blockPicker.insertIndex + i,
					replace: this.replaceBlocks
				});
			});

			if(this.selected.length) {
				this.hideBlockPicker();
				this.selected = [];
				// wait for next tick to sync mutation
				// then another tick for position update
				Vue.nextTick(() =>
					Vue.nextTick(() => this.$bus.$emit('block:updateBlockOverlays'))
				);
			}


		},

		addThisBlockType({ name, version = 1, index, replace = false }) {
			
			// if we are replacing a block then remove any validation issues it has first
			if (replace) {
				let blockId = this.blocks[this.blockPicker.insertRegion][this.blockPicker.insertSection].blocks[index].id;
				this.deleteBlockValidationIssue(blockId);
			}

			const block = {
				/* eslint-disable camelcase */
				definition_name: name,
				definition_version: version,
				/* eslint-enable camelcase */
				id: uuid(),
				fields: {}
			};

			this.addBlock({
				index,
				block,
				region: this.blockPicker.insertRegion,
				sectionIndex: this.blockPicker.insertSection,
				sectionName: 'unknown', // TODO addBlock should not need this, sections should be setup on loading page if missing.
				replace: replace
			});
		},

		cancel() {
			this.hideBlockPicker();
			this.selected = [];
		}
	}
};
</script>
