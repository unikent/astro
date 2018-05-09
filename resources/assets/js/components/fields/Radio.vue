<template>
	<div>
		<el-radio-group v-model="value" size="small">
			<el-radio
				v-for="option in options"
				:label="option.value"
				:key="option.value"
			>
				{{ option.label }}
			</el-radio>
		</el-radio-group>
	</div>
</template>

<script>
import BlockField from 'components/BlockField';

export default {
	name: 'radio-field',
	extends: BlockField
};
</script>