<template>
	<el-select
		v-model="value" 
		multiple 
		:allow-create="field.allow_create ? true : false"
		:default-first-option="field.allow_create ? true : false"
		:filterable="field.filterable == false && !field.allow_create ? false : true" 
		:placeholder="options.placeholder"
		:no-data-text="field.no_data_text ? field.no_data_text : 'No data'"
	>
		<el-option
			v-for="option in options"
			:label="option.label"
			:value="option.value"
			:key="option.value"
		>
		</el-option>
	</el-select>
</template>

<script>
import BlockField from 'components/BlockField';

export default {
	name: 'multi-select-field',
	extends: BlockField
};
</script>