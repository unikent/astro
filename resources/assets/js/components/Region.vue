<template>
<div>
	<template v-if="sections">
		<region-section
			v-for="(sectionData, index) in sections"
			:region="regionID"
			:section="index"
			:key="`section-${sectionData.name}`"
			:sectionData="sectionData"
		/>
	</template>
	<empty-region v-else />
</div>
</template>

<script>
import EmptyRegion from 'components/EmptyRegion';
import RegionSection from 'components/Section';

export default {

	name: 'region',

	props: {
		name: { // The name of this region
			type: String,
			required: true
		},
		version: { 	// The version of this region's definition
			type: String,
			required: false
		}
	},
	components: {
		EmptyRegion,
		RegionSection
	},

	computed: {
		regionID() {
			return this.name + '-v' + this.version;
		},
		sections() {
			return this.$store.getters.getRegionSections(this.name + '-v' + this.version);
		}
	},
};
</script>