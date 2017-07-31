<template>
<div>
	<back-bar :title="title" />
	<div class="todo">
		Add help center menu item
	</div>
</div>
</template>

<script>
import BackBar from 'components/BackBar';

export default {
	name: 'help-centre',

	props: ['title'],

	components: {
		BackBar
	}
};
</script>