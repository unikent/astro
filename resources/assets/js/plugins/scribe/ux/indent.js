export default () => {
	return scribe => {
		scribe.el.addEventListener('keydown', e => {
			// when tabbing indent text
			if(e.keyCode === 9) {
				e.preventDefault();

				// are we inside a list?
				const listActive =
					scribe.getCommand('insertUnorderedList').queryState() ||
					scribe.getCommand('insertOrderedList').queryState();

				if(e.shiftKey) {
					// when shift is also pressed do an outdent
					scribe.getCommand('outdent').execute();
				}
				else if(listActive) {
					// when we are in a list indent it by one
					scribe.getCommand('indent').execute();
				}
				else {
					// otherwise turn this element into a list
					scribe.getCommand('insertUnorderedList').execute();
				}
			}
		});
	};
};
