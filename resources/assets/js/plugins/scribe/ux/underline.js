export default () => {
	return (scribe) => {
		const underlineCommand = scribe.getCommand('underline');

		underlineCommand.queryState = function() {
			const
				selection = new scribe.api.Selection(),
				insideElem = selection.getContaining(
					node => /^U$/.test(node.nodeName)
				);

			return scribe.api.CommandPatch.prototype.queryState.apply(this, arguments) && insideElem;
		};

		scribe.commandPatches.underline = underlineCommand;
	};
};
