const keys = {};

const isAlphaNumeric = (k) => {
	return (
		typeof k === 'string' ?
			k.match(/[a-z0-9]/) :
			k >= 65 && k <= 90 || 48 >= k && k <= 57
	);
};

const getKey = (e) => {
	if(e.key && isAlphaNumeric(e.key)) {
		return e.key.toLowerCase();
	}
	else if(e.keyCode && isAlphaNumeric(e.keyCode)) {
		return String.fromCharCode(e.keyCode).toLowerCase();
	}
	else {
		return false;
	}
};

export const onKeyDown = (ctx) => (e) => {
	const key = getKey(e);

	if(key) {
		keys[key] = true;
	}

	if((e.ctrlKey || e.metaKey) && keys.z) {
		e.preventDefault();
		ctx.undo();
	}

	if((e.ctrlKey || e.metaKey) && keys.y) {
		e.preventDefault();
		ctx.redo();
	}

	// there's a possibility the matching onkeyup event is never
	// called so we set a timer as a fallback for now
	setTimeout(() => keys[key] = false, 500);
};

export const onKeyUp = () => (e) => {
	const key = getKey(e);

	if(key) {
		keys[key] = false;
	}
};
