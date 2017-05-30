<template>
<el-tooltip content="ctrl + click to open" placement="bottom">
	<a :href="href">
		<slot></slot>
	</a>
</el-tooltip>
</template>

<script>
export default {

	name: 'link',

	props: [
		'href'
	]
};
</script>