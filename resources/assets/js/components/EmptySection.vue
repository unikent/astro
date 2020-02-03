<template>
<div v-else class="empty-region">

	<el-alert
		title="Page section"
		description="This section of the page is free for you to add one or more optional blocks. This message won't appear on your webpage, but you can add some blocks here if you'd like to."
		type="info"
		:closable="false"
		show-icon
	>
	</el-alert>

	<el-button size="large" @click="addBlocks">Add a block to this section</el-button>
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
			const
				sectionDefinition = this.sectionData ?
					Definition.getRegionSectionDefinition(this.region, this.section) : null,
				sectionConstraints = this.sectionData ?
					allowedOperations(this.sectionData.blocks, sectionDefinition) : null,
				deprecatedBlocks = sectionDefinition.deprecatedBlocks ? sectionDefinition.deprecatedBlocks : [],
				maxBlocks = sectionDefinition.max || sectionDefinition.size;

			this.showBlockPicker({
				insertIndex: 0,
				sectionIndex: this.section,
				regionName: this.region,
				blocks: sectionConstraints ? sectionConstraints.allowedBlocks : [],
				deprecatedBlocks: deprecatedBlocks,
				maxSelectableBlocks: maxBlocks
			});
		}
	}

};
</script>
