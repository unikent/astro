<template>
<div class="b-back-button">
	<span class="block-title">
		{{ title }}
	</span>
	<span class="back" @click="handleClick">
		Menu<i class="el-icon-arrow-right"></i>
	</span>
</div>
</template>

<script>
import { mapMutations } from 'vuex';

export default {

	name: 'back-bar',

	props: {
		title: {
			type: String,
			required: true
		},
		onClick: {
			type: Function
		}
	},

	methods: {
		...mapMutations([
			'revealSidebar'
		]),

		handleClick(e) {
			if(this.onClick) {
				this.onClick(e);
			}
			else {
				this.revealSidebar();
			}
		}
	}
};
</script>