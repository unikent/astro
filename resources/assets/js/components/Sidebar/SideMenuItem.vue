<template>
<li :class="{ active }">
	<el-tooltip :content="title" placement="left" :disabled="!showTooltip">
		<a href="#" @click.prevent="handleClick">
			<Icon :name="icon" className="menu-icon" />
			<span>{{ title }}</span>
		</a>
	</el-tooltip>
</li>
</template>

<script>
import { mapState, mapMutations } from 'vuex';
import Icon from 'components/Icon';

/* global setTimeout, clearTimeout */

export default {
	name: 'side-menu-item',

	props: [
		'index',
		'link',
		'icon',
		'title',
		'active',
		'onClick'
	],

	components: {
		Icon
	},

	computed: {
		...mapState({
			collapsed: state => state.sidebarCollapsed
		})
	},

	watch: {
		collapsed(isCollapsed) {
			if(isCollapsed) {
				this.timer = setTimeout(
					() => {
						this.showTooltip = true;
					},
					500
				);
			}
			else {
				this.showTooltip = false;
				clearTimeout(this.timer);
			}
		}
	},

	data() {
		return {
			showTooltip: false
		}
	},

	methods: {
		...mapMutations([
			'setBlock'
		]),

		handleClick(e) {
			return this.onClick(e, this.index);
		}
	}
};
</script>