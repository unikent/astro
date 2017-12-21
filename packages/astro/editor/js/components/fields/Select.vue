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
import baseFieldMixin from '../../mixins/baseFieldMixin';

export default {
	name: 'select-field',
	mixins: [baseFieldMixin]
};
</script>