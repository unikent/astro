<template>
<li v-if="id !== 'pages'" :class="id === active ? 'active' : ''">
	<el-tooltip :content="title" placement="left" :disabled="!showTooltip">
		<a v-if="id === 'errors' && errorCount > 0" href="#" @click.prevent="handleClick">
			<el-badge :value="errorCount" class="item">
				<icon :name="icon" className="menu-icon" />
			</el-badge>
		</a>
		<a v-else href="#" @click.prevent="handleClick">
			<icon :name="icon" className="menu-icon" />
		</a>
	</el-tooltip>
</li>
</template>

<script>
import { mapState, mapGetters, mapMutations } from 'vuex';
import Icon from 'components/Icon';

/* global setTimeout, clearTimeout */

export default {
	name: 'side-menu-item',

	props: [
		'index',
		'icon',
		'title',
		'id',
		'active',
		'onClick'
	],

	components: {
		Icon
	},

	computed: {
		...mapGetters([
			'getAllBlockErrorsCount'
		]),

		errorCount() {
			return this.getAllBlockErrorsCount;
		}
	},

	data() {
		return {
			showTooltip: false
		};
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
