/* global document */
import $ from 'jquery';

/**
 * @author Beat Temperli <beat@temper.li>
 */
export default class Toggler {
    constructor({
        dataName = 'toggle-class',
        dataTargetName = 'toggle-target'
    } = {}) {
        this.elementDataName = dataName;
        this.elementTargetName = dataTargetName;
        this.$classTogglers = $(`[data-${this.elementDataName}]`);
        return this;
    }

    initTogglers() {
        $.each(this.$classTogglers, (index, element) => {
            const $toggler = $(element);
            const toggleClass = $toggler.data(this.elementDataName);

            $toggler.on('click', (e) => {
                e.preventDefault();
                $toggler.toggleClass(toggleClass);

                if ($toggler.data(this.elementTargetName)) {
                    $($toggler.data(this.elementTargetName)).toggleClass(toggleClass);
                }
            });
        });
    }

    /**
     * Copied 1:1 from active-link.es6.js
     */
    /* eslint-disable */
    activeLink() {
        // Start by finding all potentially active links.
        const path = drupalSettings.path;
        const queryString = JSON.stringify(path.currentQuery);
        const querySelector = path.currentQuery ? `[data-drupal-link-query='${queryString}']` : ':not([data-drupal-link-query])';
        const originalSelectors = [`[data-drupal-link-system-path="${path.currentPath}"]`];
        let selectors;

        // If this is the front page, we have to check for the <front> path as
        // well.
        if (path.isFront) {
            originalSelectors.push('[data-drupal-link-system-path="<front>"]');
        }

        // Add language filtering.
        selectors = [].concat(
            // Links without any hreflang attributes (most of them).
            originalSelectors.map(selector => `${selector}:not([hreflang])`),
            // Links with hreflang equals to the current language.
            originalSelectors.map(selector => `${selector}[hreflang="${path.currentLanguage}"]`),
        );

        // Add query string selector for pagers, exposed filters.
        selectors = selectors.map(current => current + querySelector);

        // Query the DOM.
        const activeLinks = document.querySelectorAll(selectors.join(','));
        const il = activeLinks.length;
        for (let i = 0; i < il; i++) {
            activeLinks[i].classList.add('is-active');
        }
    }
    /* eslint-enable */

    mainMenuOpenActiveSubs() {
        this.activeLink();

        const $mainMenu = $('.menu-main');
        const $subs = $mainMenu.find('.navigation.-sub');

        $.each($subs, (i, element) => {
            const $sub = $(element);
            let hasActiveItems = false;

            $.each($sub.find('.item'), (ii, item) => {
                if ($($(item).find('a')).hasClass('is-active')) {
                    hasActiveItems = true;
                }
            });

            if (hasActiveItems) {
                $sub.prev('[data-toggle-class]').addClass('-opensub');
            }
        });
    }

    /**
     * Initialize function
     */
    init() {
        this.initTogglers();
        this.mainMenuOpenActiveSubs();
    }
}
