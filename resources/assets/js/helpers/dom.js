const matchByNodeName = (node, tag) => {
	return node.nodeName && node.nodeName.toLowerCase() === tag.toLowerCase();
};

const matchExactly = (node, search) => {
	return node === search;
};

const hasClass = (node, className) => {
	return node.classList && (
		Array.isArray(className) ?
			className.some(cls => node.classList.contains(cls)) :
			node.classList.contains(className)
	);
};

export const findParent = ({ el, match = 'node', search } = {}) => {

	switch(match) {
		case 'tag':
			match = matchByNodeName;
			break;
		case 'class':
			match = hasClass;
			break;
	}

	if(match(el, search)) {
		return el;
	}

	while(el) {
		if(match(el, search)) {
			return el;
		}
		el = el.parentNode;
	}

	return null;
};

export const getTopOffset = (el) => {
	let pos = 0;

	while(el) {
		pos += (el.offsetTop - el.scrollTop + el.clientTop);
		el = el.offsetParent;
	}

	return pos;
};

/**
 * Disables any links within a given element.
 *
 * @param      {Event}  e  An object containing details about this event.
 * @return     void
 */
export const disableLinks = (e) => {
	if(!e.ctrlKey && findParent({ el: e.target, match: 'tag', search: 'a'})) {
		e.preventDefault();
	}
};

/**
* prevents users from submitting forms in el
* while still allowing the user to interact with them
*/
export const disableForms = (el) => {
	const
		buttonElements = el.querySelectorAll('button, submit'),
		formElements = el.querySelectorAll('form');

	// disable buttons
	buttonElements.forEach((buttonElement) => {
		if(!buttonElement.classList.contains('el-button')) {
			buttonElement.setAttribute('disabled', '');
		}
	});

	// remove any form actions and methods
	formElements.forEach((formElement) => {
		formElement.removeAttribute('action');
		formElement.removeAttribute('method');
	});
};
