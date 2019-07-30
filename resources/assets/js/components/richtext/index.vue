<template>
	<div class="richtext-editor">
		<richtext-toolbar ref="toolbar" :allowed-tags="options.allowedTags || defaultConfig.allowedTags" />
		<div ref="editor" class="richtext-content" />
		<div v-if="options.enableWordCount" ref="wordCount" class="richtext-word-count">0</div>
	</div>
</template>

<script>
import Scribe from 'scribe-editor';

import { defaultConfig, addScribePlugins } from 'plugins/scribe';
import RichtextToolbar from './Toolbar';

export default {

	props: [
		'options',
		'value'
	],

	components: {
		RichtextToolbar
	},

	created() {
		this.defaultConfig = defaultConfig;
	},

	watch: {
		value(value, oldValue) {
			if(value !== oldValue) {
				this.scribe.setContent(value);
			}
		}
	},

	mounted() {
		const scribe = new Scribe(
			this.$refs.editor, {
				undo: {
					enabled: true
				}
			}
		);

		addScribePlugins({
			...this.options,
			scribe,
			toolbar: this.$refs.toolbar.$el,
			wordCountEl: this.$refs.wordCount
		});

		scribe.setContent(this.value);

		scribe.on('scribe:content-changed', () => {
			const html = scribe.getHTML();

			if(this.value !== html) {
				this.$emit('input', html);
			}
		});

		this.scribe = scribe;
	}
};
</script>
