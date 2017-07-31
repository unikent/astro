<template>
<div>
	<back-bar :title="title" />
	<div class="todo">
		Add navigation menu item
	</div>
</div>
</template>

<script>
import BackBar from 'components/BackBar';

export default {
	name: 'navigation',

	props: ['title'],

	components: {
		BackBar
	}
};
</script>