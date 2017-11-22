<template>
<div v-else class="empty-region">
	<el-button size="large" @click="addBlocks">Add block(s) to this section</el-button>
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