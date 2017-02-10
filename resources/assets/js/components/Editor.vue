<template>
<div class="editor">
	<header>Sup</header>
	<div class="editor-body">
		<iframe class="editor-content" :src="getUrl" frameborder="0"></iframe>
		<nav class="editor-nav editor-sidebar">
			<page-list></page-list>
		</nav>
		<aside class="editor-component-list editor-sidebar">
			<block-sidebar></block-sidebar>
		</aside>
	</div>
	<footer class="b-bar">
		<button>Switch</button>
		<button class="b-button">Save</button>
	</footer>
</div>
</template>

<script>
	import PageList from './PageList.vue';
	import BlockSidebar from './BlockSidebar.vue';

	export default {
		name: 'editor',

		components: {
			PageList,
			BlockSidebar
		},
		computed: {
			getUrl() { return `${window.Laravel.base}/preview`;}
		}
	}

const isTopWindow = window.self === window.top;

if(isTopWindow) {
	window.bSharedState = {};
}
</script>