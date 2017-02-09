<template>
<div class="editor">
	<header></header>
	<div class="editor-body">
		<iframe class="editor-content" src="/preview" frameborder="0"></iframe>
		<nav class="editor-nav editor-sidebar">
			<page-list></page-list>
		</nav>
		<aside class="editor-component-list editor-sidebar">
			<block-list></block-list>
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
	import BlockList from './BlockList.vue';

	export default {
		name: 'editor',

		components: {
			PageList,
			BlockList
		}
	}

const isTopWindow = window.self === window.top;

if(isTopWindow) {
	window.bSharedState = {};
}
</script>