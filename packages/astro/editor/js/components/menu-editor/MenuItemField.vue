<template>
<span
	class="menu-item__cell"
	:class="{ 'is-error--custom' : errors[index] && errors[index][itemKey] }"
>
	<scroll-input
		:placeholder="placeholder"
		v-model="item[itemKey]"
		@blur="validate(index)"
		@change="validate(index)"
	>
		<template slot="prepend">{{ name }}</template>
	</scroll-input>
	<div v-if="errors[index] && errors[index][itemKey]" class="el-form-item__error">
		{{ errors[index][itemKey].map((error) => error.message).join(' ') }}
	</div>
</span>
</template>

<script>
import ScrollInput from 'components/ScrollInput';

export default {
	name: 'menu-item-field',

	components: {
		ScrollInput
	},

	props: [
		'placeholder',
		'itemKey',
		'name',
		'item',
		'errors',
		'validate',
		'index'
	]
};
</script>
