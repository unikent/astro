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
			else if([8, 46].includes(e.keyCode)) {
				// are we inside a list?
				const listActive =
					scribe.getCommand('insertUnorderedList').queryState() ||
					scribe.getCommand('insertOrderedList').queryState();

				if(listActive) {

					const
						selection = new scribe.api.Selection(),
						range = selection.range;

					if(range.collapsed) {

						const parentList = selection.getContaining(
							node => node.nodeName === 'LI'
						);

						// check we're inside a list item, that the cursor is at
						// the beginning of the current "selection" and that
						// the cursor is actually within the first element of
						// our list item
						if(
							parentList &&
							selection.selection.anchorOffset === 0 &&
							parentList.childNodes.length &&
							(
								parentList.childNodes[0] === selection.selection.anchorNode ||
								parentList === selection.selection.anchorNode
							)
						) {
							e.preventDefault();
							scribe.getCommand('outdent').execute();
						}
					}
				}
			}

		});
	};
};
