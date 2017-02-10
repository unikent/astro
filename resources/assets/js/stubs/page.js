import fullWidth from './full-width-text-v1';
import blockQuote from './block-quote-v1';

let blocks = [];

for(let i = 3; i <= 20; i++) {
	blocks.push({id: i});
}

export default {
	title: 'Test page 1',
	slug: 'test-page-1',
	meta_title: 'Meta title here.',
	meta_keywords: 'keyword1, keyword2, keyword3',
	meta_description: 'Meta description here.',
	blocks: [
		{
			id: 1,
			...fullWidth
		},
		{
			id: 2,
			...blockQuote
		},
		...blocks
	],
	published: true,
	date_published: '03/02/17'
}
