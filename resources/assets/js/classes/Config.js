export default class Config {

	static options = {};

	static init(options) {
		if(
			typeof options !== 'object' ||
			options === null ||
			Array.isArray(options)
		) {
			throw Error(
				'Config #init(options: object) must be supplied with an object'
			);
		}

		// create a shallow copy of object
		// (we are no longer directly referencing the "options"
		// object but still reference child objects)
		Config.options = { ...options };
	}

	static get(key, fallback = null) {
		if(Config.options[key] === void 0) {
			return fallback;
		}

		return Config.options[key];
	}

	static set(key, value) {
		return Config.options[key] = value;
	}

	static remove(key) {
		if(Config.options[key] !== void 0) {
			delete Config.options[key];
		}
	}

	static reset() {
		Config.options = {};
	}

}
