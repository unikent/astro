<template>
	<el-select multiple v-model="value" filterable :placeholder="options.placeholder">
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