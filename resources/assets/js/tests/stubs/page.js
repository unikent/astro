import fullWidth from './full-width-text-v1';
import blockQuote from './block-quote-v1';
// import featurePanel from './feature-panel-v1';

const blockMarkup = [
	'TextBlock',
	'PanelBlock',
	'QuoteBlock',
	'TextBlock2',
	'TextBlock3',
	'PanelBlock2',
	'TextBlock',
	'PanelBlock',
	'QuoteBlock',
	'TextBlock2',
	'TextBlock3',
	'PanelBlock2',
	'TextBlock',
	'PanelBlock',
	'QuoteBlock',
	'TextBlock'
];

let blocks = [], bm = 0;

for(let i = 4; i <= 19; i++) {
	blocks.push({
		id: i,
		markup: blockMarkup[bm]
	});
	bm++;
}

// export default {
// 	title: 'Test page 1',
// 	slug: 'test-page-1',
// 	meta_title: 'Meta title here.',
// 	meta_keywords: 'keyword1, keyword2, keyword3',
// 	meta_description: 'Meta description here.',
// 	blocks: [
// 		{
// 			id: 2,
// 			...blockQuote
// 		},
// 		{
// 			id: 1,
// 			...fullWidth
// 		},
// 		{
// 			id: 3,
// 			...featurePanel
// 		},
// 		...blocks
// 	]
// };

export default {
	title: 'Test page 1',
	slug: 'test-page-1',
	meta_title: 'Meta title here.',
	meta_keywords: 'keyword1, keyword2, keyword3',
	meta_description: 'Meta description here.',
	regions: {
		main: [
			{
				id: 2,
				...blockQuote
			},
			{
				id: 1,
				...fullWidth
			},
			// {
			// 	id: 3,
			// 	...featurePanel
			// },
			...blocks
		],
		aside: [
			...blocks.splice(0, 3)
		]
	}
};
