<template>
<div>
	<template v-if="page.blocks && page.blocks[name] && page.blocks[name].length">
		<region-section
			v-for="(sectionData, index) in page.blocks[name]"
			:region="name"
			:section="index"
			:key="`section-${sectionData.name}`"
			:index="index"
		/>
	</template>
	<empty-region v-else />
</div>
</template>

<script>
import { mapState } from 'vuex';
import Block from 'components/Block';
import EmptyRegion from 'components/EmptyRegion';
import RegionSection from 'components/Section';

export default {

	name: 'region',

	props: ['name', 'version'],

	components: {
		Block,
		EmptyRegion,
		RegionSection
	},

	computed: {
		...mapState({
			page: state => state.page.pageData
		})
	},

	methods: {

	}

};
</script>