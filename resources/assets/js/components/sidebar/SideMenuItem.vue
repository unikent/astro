<template>
<li v-if="id!=='pages'" :class="id === active ? 'active' : ''">
	<el-tooltip :content="title" placement="left" :disabled="!showTooltip">
		<a v-if="id==='errors'" href="#" @click.prevent="handleClick">
			<el-badge :value=errorCount class="item">
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
import { mapState, mapMutations, mapGetters } from 'vuex';
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
		...mapState({
			collapsed: state => state.sidebarCollapsed,
			invalidBlocks: state => state.page.invalidBlocks,

		}),

		validationIssueCount() {
			return this.errorCount;
		}
		
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
		}, 
		invalidBlocks(blocks) {
			console.log('invalid blocks has changed');
			console.dir(blocks);
			this.errorCount = blocks.length;
		}
	},

	data() {
		return {
			showTooltip: false,
			errorCount: 0
			// errorCount: this.invalidBlocks(),
		}
	},

	methods: {
		...mapMutations([
			'setBlock'
		]),

		handleClick(e) {
			return this.onClick(e, this.index);
		},

	}
};
</script>
