import $ from 'jquery';

/**
 * Email-Obfuscator.
 * @see custom Drupal-module "cevi_base"
 */
export default class Obfuscator {
    constructor({
        dataName = 'obfuscate',
        dataPhoneName = 'obfuscate-phone',
        dataStart = 'start',
        dataEnd = 'end',
        replace = /\/c\//g
    } = {}) {
        this.elementDataName = dataName;
        this.elementPhoneDataName = dataPhoneName;
        this.dataStart = dataStart;
        this.dataEnd = dataEnd;
        this.replace = replace;

        return this;
    }

    /**
     * Deobfuscate emailadress in a single object.
     * @param element
     */
    deobfuscateElement(element) {
        const $element = $(element);
        let output = '';

        if (typeof $element.data(this.elementPhoneDataName) === 'undefined') {
            const emailStartObfuscated = $element.data(this.dataStart);
            const emailEndObfuscated = $element.data(this.dataEnd);
            const emailStart = this.replaceDots(emailStartObfuscated);
            const emailEnd = this.replaceDots(emailEndObfuscated);
            output = `${emailStart}@${emailEnd}`;

            // Add it to element as href.
            $element.attr('href', `mailto:${output}`);
        } else {
            // phone
            const length = $element.data('phone-length');

            for (let i = 0; i < length; i += 1) {
                output += $element.data(`phone-${i}`);
                if (i < length - 1) {
                    output += ' ';
                }
            }

            // Add it to element as href.
            $element.attr('href', `tel:${output.replace(/ /g, '')}`);
        }

        // Replace email in content of element.
        const elementsContentObfuscated = $element.html();
        const elementsContent = elementsContentObfuscated.replace(this.replace, output);
        $element.html(elementsContent);
    }

    /**
     * Start the deobfuscator if needed.
     */
    deobfuscator() {
        const $obfuscatedElements = $(`[data-${this.elementDataName}]`);

        if ($obfuscatedElements.length < 1) {
            return;
        }

        $.each($obfuscatedElements, (index, element) => {
            this.deobfuscateElement(element);
        });
    }

    /**
     * Initialize function
     */
    init() {
        this.deobfuscator();
    }

    replaceDots(string) {
        return string.replace(this.replace, '.');
    }
}
