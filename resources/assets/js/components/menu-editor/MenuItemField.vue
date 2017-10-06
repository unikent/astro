<template>
<span
	class="menu-item__cell"
	:class="{ 'is-error--custom' : errors[index] && errors[index][nestedKey] }"
>
	<scroll-input
		:placeholder="placeholder"
		v-model="item[nestedKey]"
		@blur="validate(index)"
		@change="validate(index)"
	>
		<template slot="prepend">{{ name }}</template>
	</scroll-input>
	<div v-if="errors[index] && errors[index][nestedKey]" class="el-form-item__error">
		{{ errors[index][nestedKey].map((error) => error.message).join(' ') }}
	</div>
</span>
</template>

<script>
import ScrollInput from 'components/ScrollInput';

export default {
	name: 'site-page-links',

	components: {
		ScrollInput
	},

	props: [
		'placeholder',
		'nestedKey',
		'name',
		'item',
		'errors',
		'validate',
		'index'
	]
};
</script>
