import { selectClosestWord } from 'plugins/scribe/ux/select-closest-word';

export default () => {
	return scribe => {
		scribe.el.addEventListener('keydown', e => {
			// Select closest word when common shortcuts are triggered
			// (ctrl + b or ctrl + i)
			if(e.ctrlKey || e.metaKey) {
				let command = null;

				switch(e.keyCode) {
					case 66: // b
						command = 'bold';
						break;

					case 73: // i
						command = 'italic';
						break;
				}

				if(command) {
					selectClosestWord(scribe, command);
				}
			}
		});
	};
};
