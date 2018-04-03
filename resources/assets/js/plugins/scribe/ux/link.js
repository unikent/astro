import { eventBus } from 'plugins/eventbus';

export default () => scribe => {
	var linkPromptCommand = new scribe.api.Command('createLink');

	const execute = (selection, anchorNode, isCollapsed) => ({ text, value, type }) => {
		const
			range = selection.range,
			placeholderHref = 'http://__replace__.me';

		if(anchorNode) {
			range.selectNode(anchorNode);
			selection.selection.removeAllRanges();
			selection.selection.addRange(range);

			scribe.api.Command.prototype.execute.call(
				linkPromptCommand,
				placeholderHref
			);
		}
		else if(isCollapsed) {
			const a = document.createElement('a');
			a.setAttribute('href', value);
			a.setAttribute('data-link-type', type);
			a.textContent = text;

			range.insertNode(a);

			const newRange = document.createRange();
			newRange.setStartBefore(a);
			newRange.setEndAfter(a);

			selection.selection.removeAllRanges();
			selection.selection.addRange(newRange);
		}
		else {
			selection.selection.removeAllRanges();
			selection.selection.addRange(range);

			scribe.api.Command.prototype.execute.call(
				linkPromptCommand,
				placeholderHref
			);
		}

		// this is gross but there isn't a good alternative
		const link = scribe.el.querySelector(`a[href="${placeholderHref}"]`);

		if(link) {
			link.setAttribute('href', value);
			link.setAttribute('data-link-type', type);
		}
	};

	linkPromptCommand.execute = function() {
		const
			selection = new scribe.api.Selection(),
			anchorNode = selection.getContaining(node => node.nodeName === 'A'),
			isCollapsed = selection.range.collapsed,
			hideTextInputs = anchorNode || !isCollapsed;

		eventBus.$emit('add-link-modal:show', {
			callback: execute(selection, anchorNode, isCollapsed),
			hideTextInputs
		});
	};

	linkPromptCommand.queryState = function() {
		return !!(new scribe.api.Selection()).getContaining(
			node => node.nodeName === 'A'
		);
	};

	scribe.commands.linkPrompt = linkPromptCommand;
};
