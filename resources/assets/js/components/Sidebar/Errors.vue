<template>
<div>
	<back-bar :title="title" />
	<ul class="validation-errors">
		<li class="validation-errors__item">
			<el-tag type="danger"><i class="el-icon-warning"></i><a href="#" class="validation-errors__link">Call-to-action</a></el-tag>
		</li>
		<li class="validation-errors__item">
			<el-tag type="success"><i class="el-icon-circle-check"></i><a href="#" class="validation-errors__link">Site search</a></el-tag>
		</li>
		<li class="validation-errors__item">
			<el-tag type="success"><i class="el-icon-circle-check"></i><a href="#" class="validation-errors__link">Key links</a></el-tag>
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
