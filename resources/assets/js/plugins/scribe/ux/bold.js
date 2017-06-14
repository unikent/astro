export default () => {
	return scribe => {
		let boldCommand = scribe.getCommand('bold');

		boldCommand.queryState = function() {
			const
				selection = new scribe.api.Selection(),
				insideHeading = selection.getContaining(node => {
					return /^(H[1-6])$/.test(node.nodeName);
				}),
				insideElem = selection.getContaining(node => {
					return /^(STRONG|B)$/.test(node.nodeName);
				});

			return scribe.api.Command.prototype.queryState.apply(this, arguments) && insideElem && !insideHeading;
		};
	};
};
