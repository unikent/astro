<template>
<aside
	:style="{
		width: `${split}px`,
		marginRight: sidebarOpen ? 0 : `-${split}px`
	}"
	class="editor-component-list editor-sidebar"
	:class="{
		'editor-component-list--is-over': sidebarHover || !sidebarOpen,
		'editor-component-list--is-collapsed': !sidebarOpen,
		'editor-component-list--is-dragging': dragging
	}"
	@mouseenter="sidebarHover = true"
	@mouseleave="sidebarHover = false"
	ref="side"
>

	<div class="right-collapse" @click="sidebarOpen = !sidebarOpen">
		<i :class="{'el-icon-arrow-right' : sidebarOpen, 'el-icon-arrow-left' : !sidebarOpen}" :style="{ marginLeft: sidebarOpen ? '5px' : '3px'}"></i>
	</div>

	<div class="sidebar-wrapper">

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

		<section class="sidebar collapsed"">
			<ul class="app-sidebar" role="navigation">
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

	</div>

	<div
		class="resize-sidebar"
		@mousedown="dragStart"
	/>
</aside>
</template>


<script>
import { mapState, mapMutations } from 'vuex';

import Icon from 'components/Icon';
import SideMenuItem from 'components/Sidebar/SideMenuItem';
import PageList from 'components/PageList';
import BlockSidebar from 'components/Sidebar/BlockSidebar';
import Navigation from 'components/Sidebar/Navigation';
import Settings from 'components/Sidebar/Settings';
import HelpCentre from 'components/Sidebar/HelpCentre';

import { clamp } from 'classes/helpers';

/* global document */

export default {
	name: 'sidebar',

	components: {
		Icon,
		SideMenuItem
	},

	data() {
		return {
			dragging: false,
			split: 398,
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
					title: 'Edit blocks',
					id: 'blocks',
					component: BlockSidebar
				}
			],

			sidebarOpen: true,
			sidebarHover: false
		};
	},

	computed: {

		...mapState({
			collapsed: state => state.sidebarCollapsed,
			page: state => state.page.pageData,
			pageLoaded: state => state.page.loaded,
			activeMenuItem: state => state.menu.active
		})
	},

	methods: {
		...mapMutations([
			'showIframeOverlay',
			'collapseSidebar',
			'updateMenuActive'
		]),

		dragStart(e) {
			this.dragging = true;
			this.startX = e.pageX;
			this.startSplit = this.split;
			this.maxWidth = document.body.offsetWidth / 2;
			this.showIframeOverlay();
			document.addEventListener('mousemove', this.dragMove);
			document.addEventListener('mouseup', this.dragEnd);
		},

		dragMove(e) {
			if(this.dragging) {
				e.preventDefault(e);

				this.split = clamp({
					val: this.startSplit + this.startX - e.pageX,
					min: 398,
					max: this.maxWidth
				});
			}
		},

		dragEnd() {
			this.showIframeOverlay(false);
			this.dragging = false;
			document.removeEventListener('mousemove', this.dragMove);
			document.removeEventListener('mouseup', this.dragEnd);
		},

		openItem(e, index) {
			this.collapseSidebar();
			this.updateMenuActive(this.menu[index].id);
		}
	}
};
</script>
