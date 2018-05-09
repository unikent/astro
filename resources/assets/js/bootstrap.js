import Config from './classes/Config';
import { win } from './classes/helpers';

if (win.astro) {
    Config.init(win.astro);
}
