<template>
<div>
	<back-bar :title="title" />
	<div class="todo">
		Add UI settings menu item
	</div>
</div>
</template>

<script>
import BackBar from 'components/BackBar';

export default {
	name: 'settings',

	props: ['title'],

	components: {
		BackBar
	}
};
</script>