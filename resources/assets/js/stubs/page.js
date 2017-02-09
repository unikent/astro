import fullWidth from './full-width-text-v1';
import blockQuote from './block-quote-v1';

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
		{
			id: 3,
			// ...featurePanel
		},
		{
			id: 4,
			// ...featurePanel
		},
		{
			id: 5,
			// ...featurePanel
		},
		{
			id: 6,
			// ...featurePanel
		},
		{
			id: 7,
			// ...featurePanel
		}
	],
	published: true,
	date_published: '03/02/17'
}
