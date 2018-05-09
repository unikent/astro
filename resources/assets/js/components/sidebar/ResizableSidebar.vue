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
>

	<div class="right-collapse" @click="sidebarOpen = !sidebarOpen">
		<i :class="{'el-icon-arrow-right' : sidebarOpen, 'el-icon-arrow-left' : !sidebarOpen}" :style="{ marginLeft: sidebarOpen ? '5px' : '3px'}"></i>
	</div>

	<div class="sidebar-wrapper">
		<slot></slot>
	</div>

	<div
		class="resize-sidebar"
		@mousedown="dragStart"
	/>
</aside>
</template>


<script>
import { mapMutations } from 'vuex';

import { clamp } from 'classes/helpers';

/* global document */

export default {
	name: 'resizable-sidebar',

	data() {
		return {
			dragging: false,
			split: 398,
			sidebarOpen: true,
			sidebarHover: false
		};
	},

	methods: {
		...mapMutations([
			'showIframeOverlay'
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
		}
	}
};
</script>
