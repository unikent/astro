<template>
	<div
		class="b-block-container"
		:id="blockIdentifier"
		@mouseenter="showHoverOverlay"
		@mouseleave="hideHoverOverlay"
		@click="editBlock"
	>
		<div class="block">
			<component
				v-if="type !== 'placeholder'"
				:is="currentView"
				:fields="blockData.fields"
			/>
			<!-- placeholder element -->
			<div v-else class="placeholder-block" />
		</div>
	</div>
</template>

<script>
import { mapState, mapGetters, mapMutations } from 'vuex';
import imagesLoaded from 'imagesloaded';

import { blocks } from 'helpers/themeExports';
import { disableForms, findParent } from 'helpers/dom';

export default {

	name: 'block',

	props: {

		// The index of the block in its section
		index: {
			type: Number,
			required: true
		},

		// The name of the region containing the block
		region: {
			type: String,
			required: true
		},

		// The index in its region of the section containing the block
		section: {
			type: Number,
			required: true
		},

		// The type of the block
		type: {
			type: String
		},

		// The block data, including fields
		blockData: {
			type: Object,
			required: true
		},

		// The name of the section containing the block
		sectionName: {
			type: String,
			required: true
		},

		// unique identifier for this block
		uuid: {
			type: [Number, String],
			required: true
		}

	},

	watch: {
		index(index, oldIndex) {
			// set the selected block index when the currently selected block moves
			if(this.$store.state.contenteditor.currentBlockId === this.uuid) {
				// TODO: do this as part of an action when block is added/moved/removed
				// right now there is a brief moment when a different block is selected
				this.$store.commit('setCurrentBlockIndex', index);
			}

			if(index !== oldIndex) {
				this.$store.commit('updateBlockErrorIndex', {
					blockId: this.uuid,
					blockIndex: index
				});
			}
		}
	},

	data() {
		return {
			currentView: blocks[this.type] ? blocks[this.type] : {
				template: `
					<div class="missing-definition-warning">
						Missing "${this.type}" block type
					</div>
				`
			}
		}
	},

	computed: {
		blockIdentifier() {
			return `block_${this.uuid}`;
		},

		blockInfo() {
			return {
				regionName: this.region,
				sectionIndex: this.section,
				sectionName: this.sectionName,
				blockIndex: this.index,
				blockId: this.uuid
			};
		}
	},

	mounted() {
		disableForms(this.$el);
	},

	methods: {
		...mapMutations([
			'changeBlock'
		]),

		editBlock() {
			this.$store.dispatch('changeBlock', this.blockInfo);
		},

		showHoverOverlay() {
			this.$bus.$emit('block:showHoverOverlay', this.blockInfo);
		},

		hideHoverOverlay(e) {
			if(
				e.relatedTarget &&
				!findParent({
					el: e.relatedTarget,
					match: 'class',
					search: [
						'block-overlay',
						'b-block-container',
						'el-tooltip__popper',
						'el-dropdown-menu'
					]
				})
			) {
				this.$bus.$emit('block:hideHoverOverlay');
			}
		}
	}
};
</script>
