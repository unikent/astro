/* global DOMParser */

export default (wordCountEl) => {
	return scribe => {

		const setScribeWordCount = () => {
			const html = new DOMParser().parseFromString(scribe.el.innerHTML || '', 'text/html');
			wordCountEl.innerText = html.body.textContent.length;
		};

		setScribeWordCount();

		scribe.on('scribe:content-changed', () => {
			setScribeWordCount();
		});

	};
};
