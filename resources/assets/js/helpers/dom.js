const matchByNodeName = (node, search) => {
	return node.nodeName.toLowerCase() === search;
};

const matchExactly = (node, search) => {
	return node === search;
};

export const findParent = (searchFor, el, exact = false) => {
	const match = exact ? matchExactly : matchByNodeName;

	if(match(el, searchFor)) {
		return el;
	}

	while(el) {
		if(match(el, searchFor)) {
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
	if(!e.ctrlKey && findParent('a', e.target, false)) {
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
