export default () => {
	return scribe => {
		let unlinkCommand = scribe.getCommand('unlink');

		unlinkCommand.queryEnabled = function() {
			const
				selection = new scribe.api.Selection(),
				insideElem = selection.getContaining(
					node => node.nodeName === 'A'
				);

			return scribe.api.CommandPatch.prototype.queryEnabled.apply(this, arguments) || insideElem;
		};

		scribe.commandPatches.unlink = unlinkCommand;
	};
};
