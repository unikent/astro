<template>
<el-dialog
	class="tabbed-dialog"
	title="Add block(s)"
	top="5%"
	:visible.sync="visible"
>
	<el-tabs type="border-card">
		<el-tab-pane label="All blocks">
			<p>Select one or more blocks from below that you'd like to add to the page.</p>
			<el-alert
				v-if="replaceBlocks" 
				title="Replacing a block with another one means you will permanently lose your changes to the original block." 
				type="warning"
				:closable="false" 
				show-icon
			></el-alert>
			<div class="el-dialog__footer">
				<el-button @click="cancel">Cancel</el-button>
				<el-button type="primary" @click="addBlocks">Add selected blocks to the page</el-button>
			</div>
			<picker-list
					:selectedOptions="selected"
					:options="availableBlocks"
					:maxSelectableOptions="maxSelectableBlocks"
			/>
		</el-tab-pane>
	</el-tabs>

	<span slot="footer" class="dialog-footer">
		<el-button @click="cancel">Cancel</el-button>
		<el-button type="primary" @click="addBlocks">Add selected blocks to the page</el-button>
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
			maxSelectableBlocks: state => state.blockPicker.maxSelectableBlocks,
			replaceBlocks: state => state.blockPicker.replaceBlocks,
			allBlocks: state => state.definition.blockDefinitions
		}),

		/**
		 * Get the blocks currently available to be displayed in the block picker.
		 * @returns {Array}
		 */
		availableBlocks() {
			let blocks = {};
			if(this.allowedBlocks) {
				for(let i in this.allowedBlocks) {
					let blockId = this.allowedBlocks[i];

					if (this.allBlocks[blockId]) {
						blocks[blockId] = this.allBlocks[blockId];
					}
					else if(this.allBlocks[blockId + '-v1']) {
						blocks[blockId + '-v1'] = this.allBlocks[blockId + '-v1'];
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
			'addBlock'
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
					Vue.nextTick(() => this.$bus.$emit('block:updateOverlay'))
				);
			}
		},

		addThisBlockType({ name, version = 1, index, replace = false }) {

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
