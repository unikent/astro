export default class Config {

	static options = {};

	static init(options) {
		Config.options = options;
	}

	static get(key, fallback = null) {
		return Config.options[key] || fallback;
	}

	static set(key, value) {
		Config.options[key] = value;
	}

	static remove(key) {
		if(Config.options[key]) {
			delete Config.options[key];
		}
	}

}
