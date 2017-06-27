<template>
<div>
	<template v-if="page.blocks && page.blocks[name]">
		<block
			v-for="(blockData, index) in page.blocks[name]"
			v-if="blockData"
			:key="`block-${blockData.id}`"
			:type="getBlockType(blockData)"
			:index="index"
			:blockData="blockData"
		/>
	</template>
</div>
</template>

<script>
import { mapState } from 'vuex';
import Block from './Block';

export default {

	name: 'region',

	props: ['name', 'version'],

	components: {
		Block
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