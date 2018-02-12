<template>
	<el-radio-group v-model="value" size="small">
		<el-radio-button
			v-for="option in options"
			:label="option.value"
			:key="option.value"
		>
			{{ option.label }}
		</el-radio-button>
	</el-radio-group>
</template>

<script>
import BlockField from 'components/BlockField';

export default {
	name: 'button-group-field',
	extends: BlockField
};
</script>