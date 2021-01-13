/* global window */
// Import the components.
import Logo from './Components/Logo';
import Obfuscator from './Components/Obfuscator';
import Slider from './Components/Slider';
import Toggler from './Components/Toggler';

// Import the helpers.
import Event from './Helper/Event';

class App {
    static registerHelpers() {
        Event.registerGlobalWindowEvents();
    }

    static registerComponents() {
        // Self-initiating components:
        new Logo(); // eslint-disable-line no-new

        // Non-initiating components:
        const obfuscator = new Obfuscator();
        const slider = new Slider();
        const toggler = new Toggler();

        obfuscator.init();
        slider.init();
        toggler.init();
    }

    constructor() {
        App.registerHelpers();
        App.registerComponents();
    }
}

window.App = new App();
