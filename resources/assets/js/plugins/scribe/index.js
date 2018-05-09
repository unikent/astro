import scribePluginInlineStyles from 'scribe-plugin-inline-styles-to-elements';
import scribePluginFormatterPlainTextConvertNewLinesToHtml from 'scribe-plugin-formatter-plain-text-convert-new-lines-to-html';
import scribePluginHeadingCommand from 'scribe-plugin-heading-command';
import scribePluginSanitizer from 'scribe-plugin-sanitizer';

import scribePluginToolbar from 'plugins/scribe/toolbar';
import scribePluginUnderlineCommand from 'plugins/scribe/ux/underline';
import scribePluginBoldCommand from 'plugins/scribe/ux/bold';
import scribePluginLinkCommand from 'plugins/scribe/ux/link';
import scribePluginUnlinkCommand from 'plugins/scribe/ux/unlink';
import scribePluginIndentCommand from 'plugins/scribe/ux/indent';
import scribePluginAddClasses from 'plugins/scribe/ux/add-classes';
import scribePluginKeyboardShortcuts from 'plugins/scribe/ux/keyboard-shortcuts';

export const defaultConfig = {
	allowedTags: ['h3', 'b', 'i', 'ul', 'ol', 'li', 'a'],
	attributes: {
		a: {
			href: true,
			target: '_blank',
			rel: true,
			'data-link-type':  true
		},
		ul: {
			class: 'bullet-list'
		},
		ol: {
			class: 'numbered-list'
		}
	}
};

/**
 * Adds all of our scribe plugins, pre-built and custom.
 *
 * TODO: make the logic for toggling commands a little nicer
 *
 * @param      {object}  options  The options from our rich text definition.
 */
export const addScribePlugins = (options) => {
	const
		{ scribe } = options,
		config = {
			allowedTags: options.allowedTags || defaultConfig.allowedTags,
			attributes: options.attributes || defaultConfig.attributes
		};

	scribe.use(scribePluginToolbar(options.toolbar));
	scribe.use(scribePluginInlineStyles());
	scribe.use(scribePluginFormatterPlainTextConvertNewLinesToHtml());
	scribe.use(scribePluginAddClasses());

	scribe.use(scribePluginKeyboardShortcuts());

	// add commands for each heading tag enabled
	config.allowedTags
		.filter(tag => tag.match(/^h[1-6]$/))
		.forEach(tag => scribe.use(
			scribePluginHeadingCommand(Number.parseInt(tag.replace('h', '')))
		));

	if(config.allowedTags.includes('b')) {
		scribe.use(scribePluginBoldCommand());
	}

	if(config.allowedTags.includes('li')) {
		scribe.use(scribePluginIndentCommand());
	}

	if(config.allowedTags.includes('a')) {
		scribe.use(scribePluginLinkCommand());
		scribe.use(scribePluginUnlinkCommand());
	}

	if(config.allowedTags.includes('u')) {
		scribe.use(scribePluginUnderlineCommand());
	}

	const attributesToObject = (tag) => {
		const
			attributes = defaultConfig.attributes[tag],
			obj = {};

		if(Array.isArray(attributes)) {
			attributes.forEach(attr => obj[attr] = true);
		}
		else if(attributes) {
			return attributes;
		}

		return obj;
	};

	const allowedTags = {
		p: {},
		strong: {},
		em: {},
		table: {
			class: 'table'
		},
		tbody: {},
		tr: {},
		td: {},
		th: {},
		thead: {},
		tfoot: {}
	};

	config.allowedTags.forEach(tag => {
		allowedTags[tag] = attributesToObject(tag);
	});

	scribe.use(scribePluginSanitizer({
		tags: allowedTags
	}));
};
