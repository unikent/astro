import Config from './classes/Config';
import { win, isIframe } from './classes/helpers';

if(win.astro) {
	Config.init(isIframe ? win.top.astro : win.astro);
}
