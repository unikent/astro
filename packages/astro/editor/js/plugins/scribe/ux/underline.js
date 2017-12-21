export default () => {
	return (scribe) => {
		var underlineCommand = scribe.getCommand('underline');

		underlineCommand.queryState = function() {
			var selection = new scribe.api.Selection();

			var insideElem = selection.getContaining(node => {
				return /^U$/.test(node.nodeName);
			});

			return scribe.api.CommandPatch.prototype.queryState.apply(this, arguments) && insideElem;
		};

		scribe.commandPatches.underline = underlineCommand;
	};
};
