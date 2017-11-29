<template>
<div>
	<template v-if="sectionData && sectionData.blocks && sectionData.blocks.length">
		<block
			v-for="(blockData, index) in sectionData.blocks"
			v-if="blockData"
			:region="region"
			:section="section"
			:sectionName="sectionData.name"
			:key="`block-${blockData.id}`"
			:type="getBlockType(blockData)"
			:index="index"
			:blockData="blockData"
		/>
	</template>
	<empty-section
			:region="region"
			:section="section"
			:sectionData="sectionData"
			v-else />
</div>
</template>

<script>
import Block from 'components/Block';
import EmptySection from 'components/EmptySection';

export default {

	name: 'section',

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
	components: {
		Block,
		EmptySection
	},

	methods: {

		getBlockType(block) {
			return (
				Object.keys(block).length === 0 ?
					'placeholder' :
					`${block.definition_name}-v${block.definition_version}`
			);
		}

	}

};
</script>