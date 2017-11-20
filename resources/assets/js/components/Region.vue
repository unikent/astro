<template>
<div>
	<template v-if="sections">
		<region-section
			v-for="(sectionData, index) in sections"
			:region="name"
			:section="index"
			:key="`section-${sectionData.name}`"
			:index="index"
			:sectionData="sectionData"
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

	props: {
		name: { // The name of this region
			type: String,
			required: true
		},
		version: { 	// The version of this region's definition (not currently used?)
			type: String,
			required: false
		}
	},
	components: {
		EmptyRegion,
		RegionSection
	},

	computed: {
		sections() {
			return this.$store.getters.getRegionSections(this.name);
		}
	},
};
</script>