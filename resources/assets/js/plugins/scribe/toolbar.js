/**
 * Loosely based on guardian/scribe-plugin-toolbar.
 */
import { selectClosestWord } from 'plugins/scribe/ux/select-closest-word';

/* global setTimeout */

export default (toolbarNode) => {

	return (scribe) => {

		const
			updateToolbar = button => {
				const
					command = scribe.getCommand(button.dataset.commandName),
					selection = new scribe.api.Selection();

				if(selection.range && command.queryState(button.dataset.commandValue)) {
					button.classList.add('active');
				}
				else {
					button.classList.remove('active');
				}

				if(!selection.range || command.queryEnabled()) {
					button.removeAttribute('disabled');
				}
				else {
					button.setAttribute('disabled', 'disabled');
				}
			},
			buttons = toolbarNode.querySelectorAll('[data-command-name]');

		[...buttons].forEach(button => {

			if(!button.dataset.commandIgnore) {
				button.addEventListener('mousedown', (e) => {

					selectClosestWord(scribe, button.dataset.commandName);

					scribe
						.getCommand(button.dataset.commandName)
						.execute(button.dataset.commandValue);

					e.preventDefault();
				});
			}

			const update = () => updateToolbar(button);

			button.addEventListener('blur', () => setTimeout(update, 0));

			scribe.el.addEventListener('keyup', update);
			scribe.el.addEventListener('mouseup', () => setTimeout(update, 0));

			scribe.el.addEventListener('focus', update);
			scribe.el.addEventListener('blur', () => setTimeout(update, 0));

			scribe.on('scribe:content-changed', update);
		});
	};
};
