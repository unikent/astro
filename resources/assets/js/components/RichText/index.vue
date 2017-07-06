<template>
<div class="richtext-editor">
	<richtext-toolbar ref="toolbar" />
	<div ref="editor" class="richtext-content" />
</div>
</template>

<script>
import Scribe from 'scribe-editor';

import scribePluginFormatterPlainTextConvertNewLinesToHtml from 'scribe-plugin-formatter-plain-text-convert-new-lines-to-html';
import scribePluginSanitizer from 'scribe-plugin-sanitizer';
import scribePluginInlineStyles from 'scribe-plugin-inline-styles-to-elements';
import scribePluginHeadingCommand from 'scribe-plugin-heading-command';
import scribePluginLinkPromptCommand from 'scribe-plugin-link-prompt-command';

import scribePluginToolbar from 'plugins/scribe/toolbar';
import scribePluginUnderlineCommand from 'plugins/scribe/ux/underline';
import scribePluginBoldCommand from 'plugins/scribe/ux/bold';
import scribePluginUnlinkCommand from 'plugins/scribe/ux/unlink';

import RichtextToolbar from './Toolbar';

export default {

	props: [
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
		const
			editor = this.$refs.editor,
			scribe = new Scribe(editor, {
				undo: {
					enabled: false
				}
			});

		scribe.use(scribePluginToolbar(this.$refs.toolbar.$el));
		scribe.use(scribePluginInlineStyles());
		// Add support for h1-h6 tags
		for(var h = 1; h <= 6; h++) {
			scribe.use(scribePluginHeadingCommand(h));
		}
		scribe.use(scribePluginLinkPromptCommand());
		scribe.use(scribePluginUnderlineCommand());
		scribe.use(scribePluginBoldCommand());
		scribe.use(scribePluginUnlinkCommand());
		scribe.use(scribePluginFormatterPlainTextConvertNewLinesToHtml());

		scribe.use(scribePluginSanitizer({
			tags: {
				p: {},
				b: {},
				i: {},
				u: {},
				ul: {},
				ol: {},
				li: {},
				h1: {},
				h2: {},
				h3: {},
				h4: {},
				h5: {},
				h6: {},
				address: {},
				pre: {},
				a: { href: true },
				abbr: {},
				cite: {},
				code: {},
				em: {},
				q: {},
				s: {},
				strike: {},
				samp: {},
				span: {},
				strong: {},
				caption: {},
				col: {},
				colgroup: {},
				table: {},
				tbody: {},
				tr: {},
				td: {},
				th: {},
				tfoot: {},
				thead: {},
				sub: {},
				sup: {},
				blockquote: {}
			}
		}));

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