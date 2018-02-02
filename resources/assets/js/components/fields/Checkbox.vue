<template>
	<el-checkbox-group v-model="value">
		<el-checkbox
				v-for="option in options"
				:label="option.value"
				:key="option.value"
			>
			{{ option.label }}
		</el-checkbox>
	</el-checkbox-group>
</template>

<script>
import baseFieldMixin from '../../mixins/baseFieldMixin';

export default {
	name: 'checkbox-field',
	mixins: [baseFieldMixin]
};
</script>