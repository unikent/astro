<style scoped>
h3 {
	margin-top: 30px;
	padding-top: 20px;
	border-top: 1px solid rgba(191, 203, 217, 0.3);
}

.block-options-list {
	height: calc(100vh - 42px);
	padding: 0 20px 80px;
	overflow: auto;
	margin-bottom: 80px;
}
</style>

<template>
<div>
	<div class="b-back-button" @click="editBlock">
		<i class="el-icon-arrow-left"></i>Back
	</div>
	<div class="block-options-list">
		<h2 v-if="currentDefinition">Edit {{ currentDefinition.name }} Block</h2>

		<div v-if="currentDefinition">
			<div v-for="field in currentDefinition.fields">
				<h3>{{ field.label }}</h3>
				<component :is="getField(field.type)" :name="field.name" :field="field">
				<pre>{{ currentDefinition }}</pre>
			</div>
		</div>
	</div>
</div>
</template>

<script>
import { mapState, mapActions } from 'vuex';
import fields from './fields';
import f from 'cms-prototype-blocks';

export default {

	computed: {
		currentDefinition() {
			return this.$store.state.blockDef;
		},

		currentView() {
			return f['block-quote-v1'];
		}
	},

	methods: {
		...mapActions([
			'editBlock'
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

}
</script>