export default () => scribe => {
	scribe.registerHTMLFormatter('sanitize', (html) =>
		html
			.replace(
				/<([uo])l>/g,
				(match, p1) => `<${p1}l class="${p1 === 'u' ? 'bullet' : 'numbered'}-list">`
			)
			.replace(/<table>/g, '<table class="table">')
	);
};
