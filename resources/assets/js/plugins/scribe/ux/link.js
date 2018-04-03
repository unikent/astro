import { eventBus } from 'plugins/eventbus';

export default () => scribe => {
	var linkPromptCommand = new scribe.api.Command('createLink');

	const execute = (selection, anchorNode, isCollapsed) => ({ text, value }) => {
		const
			range = selection.range;

		if(anchorNode) {
			range.selectNode(anchorNode);
			selection.selection.removeAllRanges();
			selection.selection.addRange(range);

			scribe.api.Command.prototype.execute.call(
				linkPromptCommand,
				value
			);
		}
		else if(isCollapsed) {
			const a = document.createElement('a');
			a.setAttribute('href', value);
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
				value
			);
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
