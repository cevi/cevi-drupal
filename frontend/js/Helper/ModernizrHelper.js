/* global Modernizr */

export default class ModernizrHelper {
    /**
     * Return Boolean if website is supported.
     */
    static isWebsiteSupported() {
        Modernizr.anyflexbox = (Modernizr.flexbox || Modernizr.flexboxtweener);

        return Modernizr.anyflexbox;
    }
}
