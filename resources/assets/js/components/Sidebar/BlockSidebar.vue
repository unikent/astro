<template>
	<block-options :title="title"></block-options>
</template>

<script>
import BlockOptions from 'components/BlockOptions';

export default {
	name: 'BlockSidebar',

	props: ['title'],

	components: {
		BlockOptions
	}
};
</script>