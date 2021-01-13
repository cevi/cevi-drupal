/* global drupalSettings */

import ModernizrHelper from './ModernizrHelper';

export default class Drupal {
    /**
     * Return DrupalSettings
     */
    static getDrupalSettings() {
        // Check if the website is supported, add's some css-classes to the <html>
        ModernizrHelper.isWebsiteSupported();

        if (typeof drupalSettings !== 'undefined') {
            return drupalSettings;
        }

        // DrupalSettings are not available. For example the styleguide will not be able to load it.
        return {
            theme_path: '/../themes/custom/cevi'
        };
    }

    static getClaimData() {
        const settings = Drupal.getDrupalSettings();

        if (settings.claim === 'undefined') {
            return null;
        }

        return settings.claim;
    }
}
