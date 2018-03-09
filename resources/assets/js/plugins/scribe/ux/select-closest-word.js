const
	tagMap = {
		bold: ['B', 'STRONG'],
		unlink: ['A'],
		underline: ['U'],
		italic: ['I'],
		subscript: ['SUB'],
		superscript: ['SUP']
	},

	commandNeedsSelection = (command) => {
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
	},

	getClosestWord = (str, pos) => {
		// TODO: check for &nbsp; and other html encoded delimiters
		const
			delimiters = '\\s,;%`:\\._\\/\\(\\)\\[\\]{}<>\\|§!"#¤=\\?\\^~¨\\*′\'@￡\\$€μ';
		let
			left = str.slice(0, pos + 1).search('[^' + delimiters + ']+$'),
			right = str.slice(pos).search('[' + delimiters + ']');

		if(left === -1 || right === -1) {
			return null;
		}

		return { start: left, end: pos + right };
	},

	selectClosestWord = (scribe, commandName) => {

		if(!commandNeedsSelection(commandName)) {
			return;
		}

		let selection = new scribe.api.Selection();
		const range = selection.range;

		if(range && range.collapsed) {

			let
				tag = null,
				start = 0,
				end = null,
				node;

			if(tagMap[commandName]) {
				tag = tagMap[commandName];
			}

			if(tag && (node = selection.getContaining(node => tag.includes(node.nodeName)))) {
				range.selectNode(node);
			}
			else {
				const
					selectOffset = selection.range.endOffset,
					closestWord = getClosestWord(
						selection.range.endContainer.textContent,
						selectOffset
					);

				if(closestWord) {
					range.setStart(selection.range.endContainer, closestWord.start);
					range.setEnd(selection.range.endContainer, closestWord.end);
				}
			}

			selection.selection.removeAllRanges();
			selection.selection.addRange(range);
		}
	};

export { selectClosestWord };
