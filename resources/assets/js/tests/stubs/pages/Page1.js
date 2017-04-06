import fullWidth from './full-width-text-v1';
import blockQuote from './block-quote-v1';
import featurePanel from './feature-panel-v1';

export default {
	title: 'Test page 1',
	slug: 'test-page-1',
	meta_title: 'Meta title here.',
	meta_keywords: 'keyword1, keyword2, keyword3',
	meta_description: 'Meta description here.',
	blocks: [
		{
			id: 2,
			...blockQuote
		},
		{
			id: 1,
			...fullWidth
		},
		{
			id: 3,
			...featurePanel
		}
	]
}
