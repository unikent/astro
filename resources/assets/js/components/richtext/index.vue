<template>
	<div class="richtext-editor">
		<richtext-toolbar ref="toolbar" />
		<div ref="editor" class="richtext-content" />
	</div>
</template>

<script>
import Scribe from 'scribe-editor';

import { addScribePlugins } from 'plugins/scribe';
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
		this.content = '';
	},

	// watch: {
	// 	value(val, oldVal) {
	// 		if(val !== oldVal) {
	// 			this.content = val;
	// 			this.scribe.setHTML(val, true);
	// 		}
	// 	}
	// },

	mounted() {

		const scribe = new Scribe(
			this.$refs.editor, {
				undo: {
					enabled: false
				}
			}
		);

		addScribePlugins({
			...this.options,
			scribe,
			toolbar: this.$refs.toolbar.$el
		});

		this.content = this.value;

		scribe.setContent(this.value);

		scribe.on('scribe:content-changed', () => {
			const html = scribe.getHTML();

			if(this.content !== html) {
				this.$emit('input', this.content = html);
			}
		});

		this.scribe = scribe;
	}
};
</script>
