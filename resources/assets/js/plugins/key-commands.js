const keys = {};

const isAlphaNumeric = (k) => {
	return (
		typeof k === 'string'  ?
			k.match(/[a-z0-9]/) :
			k >= 65 && k <= 90 || 48 >= k && k <= 57
	);
};

const getKey = (e) => {
	if(e.key && isAlphaNumeric(e.key)) {
		return e.key;
	}
	else if(e.keyCode && isAlphaNumeric(e.keyCode)) {
		return String.fromCharCode(e.keyCode).toLowerCase();
	}
	else {
		return false;
	}
};

export const onKeyDown = (ctx) => {
	return (e) => {
		const key = getKey(e);

		if(key) {
			keys[key] = true;
		}

		if(e.ctrlKey && keys.z) {
			e.preventDefault();
			ctx.undo();
		}

		if(e.ctrlKey && keys.y) {
			e.preventDefault();
			ctx.redo();
		}
	};
};

export const onKeyUp = () => {
	return (e) => {
		const key = getKey(e);

		if(key) {
			keys[key] = false;
		}
	};
};
