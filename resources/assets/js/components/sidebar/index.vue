<template>
<resizable-sidebar>

	<div class="sidebar-component-wrapper">
		<component
			v-for="(item, index) in menu"
			v-if="item.component"
			v-show="item.id===activeMenuItem"
			:is="item.component"
			:key="`component-${item.link}`"
			:title="item.title"
		/>
	</div>

	<section class="sidebar">
		<ul class="sidebar__switcher" role="navigation">
			<side-menu-pages
				v-for="(item, index) in menu"
				:link="item.link"
				:icon="item.icon"
				:title="item.title"
				:id="item.id"
				:key="item.link"
				:index="index"
				:active="activeMenuItem"
				:onClick="openItem"
			/>
		</ul>
		<ul :class="activeMenuItem==='pages'?'sidebar--deactivated':''" class="sidebar__switcher" role="navigation">
			<side-menu-item
				v-for="(item, index) in menu"
				:link="item.link"
				:icon="item.icon"
				:title="item.title"
				:id="item.id"
				:key="item.link"
				:index="index"
				:active="activeMenuItem"
				:onClick="openItem"
			/>
		</ul>
	</section>

</resizable-sidebar>
</template>


<script>
import { mapState, mapMutations } from 'vuex';

import ResizableSidebar from 'components/sidebar/ResizableSidebar';
import Icon from 'components/Icon';
import SideMenuItem from 'components/sidebar/SideMenuItem';
import SideMenuPages from 'components/sidebar/SideMenuPages';
import PageList from 'components/PageList';
import EditBlock from 'components/EditBlock';
import ErrorSidebar from 'components/sidebar/Errors';
import { eventBus } from 'plugins/eventbus';

export default {
	name: 'sidebar',

	components: {
		ResizableSidebar,
		Icon,
		SideMenuItem,
		SideMenuPages
	},

	data() {
		return {
			menu: [
				{
					link: '/pages',
					icon: 'sites',
					title: 'Pages',
					id: 'pages',
					component: PageList
				},
				{
					link: '/media',
					icon: 'layers',
					title: 'Selected block options',
					id: 'blocks',
					component: EditBlock
				},
				{
					link: '/errors',
					icon: 'alert',
					title: 'Errors on current page',
					id: 'errors',
					component: ErrorSidebar
				}
			]
		};
	},

	computed: {
		...mapState({
			page: state => state.page.pageData,
			pageLoaded: state => state.page.loaded,
			activeMenuItem: state => state.menu.active
		})
	},

	methods: {
		...mapMutations([
			'updateMenuActive',
			'collapseSidebar'
		]),

		openItem(e, index) {
			this.collapseSidebar();
			this.updateMenuActive(this.menu[index].id);
		}
	},

	mounted() {
		// to enable other components to open the errors sidebar when needed
		eventBus.$on('sidebar:openErrors', () => {
			this.updateMenuActive('errors');
		});
	}
};
</script>
