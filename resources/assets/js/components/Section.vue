<template>
<div>
	<template v-if="sectionData && sectionData.blocks.length">
		<block
			v-for="(blockData, index) in sectionData.blocks"
			v-if="blockData"
			:region="region"
			:section="sectionData.name"
			:key="`block-${blockData.id}`"
			:type="getBlockType(blockData)"
			:index="index"
			:blockData="blockData"
		/>
	</template>
	<empty-section v-else />
</div>
</template>

<script>
import { mapState } from 'vuex';
import Block from 'components/Block';
import EmptySection from 'components/EmptySection';

export default {

	name: 'section',

	props: ['name', 'version', 'region', 'section','sectionData'],

	components: {
		Block,
		EmptySection
	},

	computed: {
		...mapState({
			page: state => state.page.pageData
		})
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