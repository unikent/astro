/**
 * Loosely base on guardian/scribe-plugin-toolbar.
 *
 * TODO: move logic that selects correct text into its own plugin
 */

/* global setTimeout */

function commandNeedsSelection(command) {
	switch(command) {
		case 'insertUnorderedList':
		case 'insertOrderedList':
		case 'indent':
		case 'outdent':
		case 'blockquote':
		case 'table':
		case 'h1':
		case 'h2':
		case 'h3':
		case 'h4':
		case 'h5':
		case 'h6':
			return false;
		default:
			return true;
	}
}

const tagMap = {
	bold: ['B', 'STRONG'],
	unlink: ['A'],
	underline: ['U'],
	italic: ['I'],
	subscript: ['SUB'],
	superscript: ['SUP']
}

function getClosestWord(str, pos) {
	// TODO: check for &nbsp; and other html encoded delimiters
	const
		delimiters = '\\s,;%`:\\._\\/\\(\\)\\[\\]{}<>\\|§!"#¤=\\?\\^~¨\\*′\'@￡\\$€μ';
	let
		left = str.slice(0, pos + 1).search('[^' + delimiters + ']+$'),
		right = str.slice(pos).search('[' + delimiters + ']');

	if(left === -1 || right === -1) {
		return {};
	}

	return { start: left, end: pos + right };
}

export default (toolbarNode) => {

	return (scribe) => {

		const
			updateToolbar = button => {
				const command = scribe.getCommand(button.dataset.commandName);
				let selection = new scribe.api.Selection();

				if(selection.range && command.queryState(button.dataset.commandValue)) {
					button.classList.add('active');
				}
				else {
					button.classList.remove('active');
				}

				if(!selection.range) {
					button.removeAttribute('disabled');
				}
				else if(command.queryEnabled()) {
					button.removeAttribute('disabled');
				}
				else {
					button.setAttribute('disabled', 'disabled');
				}
			},
			buttons = toolbarNode.querySelectorAll('[data-command-name]');

		[...buttons].forEach(button => {

			button.addEventListener('mousedown', (e) => {
				const command = scribe.getCommand(button.dataset.commandName);

				scribe.el.focus();

				if(commandNeedsSelection(button.dataset.commandName)) {
					let selection = new scribe.api.Selection();

					if(selection.range && selection.range.collapsed) {

						let
							tag = null,
							start = 0,
							end = null,
							node,
							range = selection.range;

						if(tagMap[button.dataset.commandName]) {
							tag = tagMap[button.dataset.commandName];
						}

						if(tag && (node = selection.getContaining(node => tag.includes(node.nodeName)))) {
							// selection.placeMarkers();
							range.selectNode(node);
						}
						else {
							const selectOffset = selection.range.endOffset;

							(
								{ start, end } = getClosestWord(
									selection.range.endContainer.textContent,
									selectOffset
								)
							);

							if(start !== void 0 || end !== void 0) {
								range.setStart(selection.range.endContainer, start);
								range.setEnd(selection.range.endContainer, end);
							}

							// selection.placeMarkers();
							// let marker = scribe.el.querySelector('em.scribe-marker');

							// console.log({
							// 	startEl: selection.range.endContainer,
							// 	startOffset: start,
							// 	endEl: marker[marker.length - 1].nextSibling,
							// 	endOffset: end - selectOffset
							// });

							// range.setStart(selection.range.endContainer, start);
							// range.setEnd(marker.nextSibling, end - selectOffset);
						}

						selection.selection.removeAllRanges();
						selection.selection.addRange(range);
					}
				}

				command.execute(button.dataset.commandValue);

				// selection.selectMarkers();

				e.preventDefault();
			});

			const update = () => updateToolbar(button);

			button.addEventListener('blur', () => setTimeout(update, 0));

			scribe.el.addEventListener('keyup', update);
			scribe.el.addEventListener('mouseup', () => setTimeout(update, 0));

			scribe.el.addEventListener('focus', update);
			scribe.el.addEventListener('blur', () => setTimeout(update, 0));

			scribe.on('scribe:content-changed', update);
		});
	};
};
