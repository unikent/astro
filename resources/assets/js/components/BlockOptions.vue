<template>
<div>
	<div class="b-back-button" @click="setBlock">
		<i class="el-icon-arrow-left"></i>Back
	</div>
	<div class="block-options-list custom-scrollbar">
		<h2 v-if="currentDefinition">Edit {{ currentDefinition.name }} Block</h2>

		<div v-if="currentDefinition">
			<div v-for="field in currentDefinition.fields">
				<h3 class="field-label">{{ field.label }}</h3>
				<component :is="getField(field.type)" :name="field.name" :field="field" />
			</div>
		</div>
	</div>
</div>
</template>

<script>
import { mapMutations } from 'vuex';
import fields from './fields';

export default {

	computed: {
		currentDefinition() {
			return this.$store.state.blockDef;
		}
	},

	methods: {
		...mapMutations([
			'setBlock'
		]),

		getField(type) {
			return (
				fields[type] ?
				fields[type] :  {
					name: type,
					template: '<div/>'
				}
			);
		}
	}

};
</script>