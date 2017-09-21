<template>
<div>
	<back-bar :title="title" />
	<ul class="validation-errors">
		<li class="validation-errors__item">
			<i class="el-icon-warning"></i><a href="#" class="validation-errors__link">Call-to-action</a>
		</li>
		<li class="validation-errors__item">
			<i class="el-icon-warning"></i><a href="#" class="validation-errors__link">Key links</a>
		</li>
	</ul>
</div>
</template>

<script>
import BackBar from 'components/BackBar';

export default {
	name: 'errors',

	props: ['title'],

	components: {
		BackBar
	}
};
</script>
