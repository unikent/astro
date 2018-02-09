<template>
	<el-select v-model="value" :placeholder="options.placeholder">
		<el-option
			v-for="option in options"
			:label="option.label"
			:value="option.value"
			:key="option.label"
		>
		</el-option>
	</el-select>
</template>

<script>
import BlockField from 'components/BlockField';

export default {
	name: 'select-field',
	extends: BlockField
};
</script>