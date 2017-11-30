<template>
<div v-else class="empty-region">

	<el-alert
		title="We left this space free for you to add some optional blocks to your page."
		description="Don't worry, this message won't appear on your webpage. But you can add a block (or maybe more) here if you'd like to."
		type="info"
		:closable="false"
		show-icon
		style="width:85%; margin: 1rem auto"
	>
	</el-alert>

	<el-button size="large" @click="addBlocks">Add a block</el-button>
</div>
</template>

<script>
import { mapMutations } from 'vuex';
import { Definition } from 'classes/helpers';
import { allowedOperations } from 'classes/SectionConstraints';
export default {

	name: 'empty-section',

	props: {
		// The name of the region containing this section
		region: {
			type: String,
			required: true
		},

		// The index in its region of this section
		section: {
			type: Number,
			required: true
		},

		// The section data
		sectionData: {
			type: Object,
			required: true
		},
	},

	methods: {

		...mapMutations([
			'showBlockPicker',
			'updateInsertIndex',
			'updateInsertRegion'
		]),

		addBlocks() {
			const sectionDefinition = this.sectionData ? Definition.getRegionSectionDefinition(this.region, this.section) : null;
			const sectionConstraints = this.sectionData ? allowedOperations(this.sectionData.blocks, sectionDefinition) : null;
			this.showBlockPicker({
				insertIndex: 0,
				sectionIndex: this.section,
				regionName: this.region,
				blocks: sectionConstraints ? sectionConstraints.allowedBlocks : []
			});
		}
	}

};
</script>
