import scribePluginInlineStyles from 'scribe-plugin-inline-styles-to-elements';
import scribePluginFormatterPlainTextConvertNewLinesToHtml from 'scribe-plugin-formatter-plain-text-convert-new-lines-to-html';
import scribePluginHeadingCommand from 'scribe-plugin-heading-command';
import scribePluginLinkPromptCommand from 'scribe-plugin-link-prompt-command';
import scribePluginSanitizer from 'scribe-plugin-sanitizer';

import scribePluginToolbar from 'plugins/scribe/toolbar';
import scribePluginUnderlineCommand from 'plugins/scribe/ux/underline';
import scribePluginBoldCommand from 'plugins/scribe/ux/bold';
import scribePluginUnlinkCommand from 'plugins/scribe/ux/unlink';

export const defaultConfig = {
	allowedTags: ['h3', 'b', 'i', 'ul', 'ol', 'li', 'a'],
	attributes: {
		a: ['href']
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

	// add commands for each heading tag enabled
	config.allowedTags
		.filter(tag => tag.match(/^h[1-6]$/))
		.forEach(tag => scribe.use(
			scribePluginHeadingCommand(Number.parseInt(tag.replace('h', '')))
		));

	if(config.allowedTags.includes('b')) {
		scribe.use(scribePluginBoldCommand());
	}

	if(config.allowedTags.includes('a')) {
		scribe.use(scribePluginLinkPromptCommand());
		scribe.use(scribePluginUnlinkCommand());
	}

	if(config.allowedTags.includes('u')) {
		scribe.use(scribePluginUnderlineCommand());
	}

	const attributesToObject = (tag) => {
		const
			attributes = defaultConfig.attributes[tag],
			obj = {};

		if(attributes) {
			attributes.forEach(attr => obj[attr] = true);
		}

		return obj;
	};

	const allowedTags = {
		p: {},
		strong: {},
		em: {},
		table: {},
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
