import api from './api';

export default (options) => {

	const config = {
		onUploadProgress(e) {
			if(e.total > 0) {
				e.percent = Math.round((e.loaded * 100) / e.total);
			}

			options.onProgress(e);
		},

		headers: options.headers || {},

    	withCredentials: !!options.withCredentials
	};

	const formData = new FormData();

	if(options.data) {
		Object.keys(options.data).map(key => {
			formData.append(key, options.data[key]);
		});
	}

	formData.append(options.filename, options.file);

	api
		.post(options.action, formData, config)
		.then(res => {
			if(res.status < 200 || res.status >= 300) {
				options.onError(res);
				return;
			}
			options.onSuccess(res);
		})
		.catch((err) => {
			options.onError(err);
		});
}