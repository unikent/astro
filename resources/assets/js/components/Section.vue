<template>
<div>
	<template v-if="page.blocks[region][section] && page.blocks[region][section].blocks.length">
		<block
			v-for="(blockData, index) in page.blocks[region][section].blocks"
			v-if="blockData"
			:region="region"
			:section="page.blocks[region][section].name"
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

	props: ['name', 'version', 'region', 'section'],

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