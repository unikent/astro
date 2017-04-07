<template>
	<el-select v-model="value" :placeholder="options.placeholder">
		<el-option
			v-for="option in options"
			:label="option.label"
			:value="option.value"
		>
		</el-option>
	</el-select>
</template>

<script>
import BaseFieldMixin from './BaseFieldMixin';

export default {
	name: 'SelectField',
	mixins: [BaseFieldMixin]
};
</script>