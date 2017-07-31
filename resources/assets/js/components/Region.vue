<template>
<div>
	<template v-if="page.blocks && page.blocks[name] && page.blocks[name].length">
		<block
			v-for="(blockData, index) in page.blocks[name]"
			v-if="blockData"
			:region="name"
			:key="`block-${blockData.id}`"
			:type="getBlockType(blockData)"
			:index="index"
			:blockData="blockData"
		/>
	</template>
	<empty-region v-else />
</div>
</template>

<script>
import { mapState } from 'vuex';
import Block from 'components/Block';
import EmptyRegion from 'components/EmptyRegion';

export default {

	name: 'region',

	props: ['name', 'version'],

	components: {
		Block,
		EmptyRegion
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