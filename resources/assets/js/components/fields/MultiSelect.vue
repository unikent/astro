<template>
	<el-select multiple v-model="value" :placeholder="options.placeholder">
		<el-option
			v-for="option in options"
			:label="option.label"
			:value="option.value"
		>
		</el-option>
	</el-select>
</template>

<script>
import baseFieldMixin from '../../mixins/baseFieldMixin';

export default {
	name: 'multi-select-field',
	mixins: [baseFieldMixin]
};
</script>