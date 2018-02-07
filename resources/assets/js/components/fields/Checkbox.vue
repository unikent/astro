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
import BlockField from 'components/BlockField';

export default {
	name: 'checkbox-field',
	extends: BlockField
};
</script>