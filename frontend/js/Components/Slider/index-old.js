/**
 * Replaced by Slider/index.js
 */

/* global document */
import $ from 'jquery';
import Event from '../../Helper/Event';

/**
 * Create a slider.
 */
export default class Slider {
    constructor({
        dataSliderName: dataSliderWrapperName = 'slideshow',
        dataSliderContentName = 'slider',
        stroke = 20,
        interval: intervalTime = 6000
    } = {}) {
        this.sliderWrapperName = dataSliderWrapperName;
        this.$sliderWrapper = $(`[data-${dataSliderWrapperName}]`);
        this.sliderContentName = dataSliderContentName;
        this.$sliderContent = this.$sliderWrapper.find(`[data-${dataSliderContentName}]`);
        this.stroke = stroke;
        this.intervalTime = intervalTime;
        this.sliderInterval = null;
        return this;
    }

    calculateSizes() {
        const paddingTop = parseInt(this.$sliderWrapper.css('padding-top'), 10);
        const paddingBottom = parseInt(this.$sliderWrapper.css('padding-bottom'), 10);

        this.sliderWidth = this.$sliderWrapper.innerWidth() + 100;
        this.sliderHeight = this.$sliderWrapper.innerHeight() - paddingTop - paddingBottom;
        this.triangleWidth = this.stroke * 4.32;
        this.triangleHeight = this.stroke * 3.2;
        this.sliderFullWidth = this.sliderWidth + this.triangleWidth + this.stroke;
    }

    prepareSliderContent() {
        const topline = `${(this.stroke / -2)} ${(this.stroke / -2)}, ${(this.sliderWidth)} ${(this.stroke / -2)}`;
        let rightline = `${this.sliderWidth} ${((this.sliderHeight / 2) - this.triangleHeight)}, `;
        rightline += `${(this.sliderWidth + this.triangleWidth)} ${(this.sliderHeight / 2)}, `;
        rightline += `${this.sliderWidth} ${((this.sliderHeight / 2) + this.triangleHeight)}`;
        const bottomline = `${this.sliderWidth} ${(this.sliderHeight + (this.stroke / 2))}, ${(this.stroke / -2)} ${(this.sliderHeight + (this.stroke / 2))}`;

        $.each(this.$sliderContent, (index, element) => {
            const $element = $(element);
            const $image = $element.find('.image');
            const backgroundImage = $image.data('image-url');
            const $svg = $image.find('svg');

            if ($svg.length > 0) {
                $svg.remove();
            }

            let svg = `<svg height="${this.sliderHeight}" width="${this.sliderFullWidth}" `;
            svg += `viewBox="0 0 ${this.sliderFullWidth} ${this.sliderHeight}">`;
            svg += `<image xlink:href="${backgroundImage}" x="0" y="0" width="100%" height="100%" `;
            svg += `preserveAspectRatio="xMidYMid slice" clip-path="url(#mask-image-${index})"/>`;
            svg += `<polygon points="${topline}, ${rightline}, ${bottomline}" stroke-width="${this.stroke}" `;
            svg += 'stroke="white" stroke-linejoin="butt" fill="transparent" />';
            svg += `<clipPath id="mask-image-${index}">`;
            svg += `<polygon points="${topline}, ${rightline}, ${bottomline}" />`;
            svg += '</clipPath>';
            svg += '</svg>';

            $image.append(svg).height(this.sliderHeight);
        });
    }

    addStylesToSheet() {
        // Remove old styles:
        let sheet = this.$sliderWrapper.find('.slider-styles');
        //
        if (sheet && sheet.length > 0) {
            sheet.remove();
        }

        // Add new styles
        sheet = document.createElement('style');
        sheet.setAttribute('id', 'slider-styles');
        sheet.innerHTML = `.slider {width: ${(this.sliderWidth + (this.stroke / 2))}px; } `;
        sheet.innerHTML += `.slider { left: ${(this.sliderWidth + (this.stroke / 2))}px; } `;
        document.body.appendChild(sheet);
    }

    renderSlider() {
        // Stop the slider firstly.
        if (this.sliderInterval) {
            clearInterval(this.sliderInterval);
        }

        this.prepareSliderContent();
        this.addStylesToSheet();

        const elements = $(this.$sliderWrapper[0]).find('.slider');
        let index = 0;
        const elementsLength = elements.length;

        function getActiveElement() {
            if (index >= elementsLength - 1) {
                index = 0;
                return 0;
            }
            index += 1;
            return index;
        }

        function getActiveElementMinus(i, minus) {
            let ii = i - minus;
            if (ii < 0) {
                ii = elementsLength + ii;
            }

            return ii;
        }

        function slide() {
            const i = getActiveElement();
            const ii = getActiveElementMinus(i, 3);

            elements[i].classList.add('-active');
            elements[ii].classList.remove('-active');
        }

        slide();

        this.sliderInterval = setInterval(() => {
            slide();
        }, this.intervalTime);
    }

    removeUnused() {
        this.$sliderWrapper = $(`[data-${this.sliderWrapperName}]`);

        $.each(this.$sliderWrapper, (index, element) => {
            if ($(element).is(':visible')) {
                this.$sliderWrapper = $(element);
            }
        });
    }

    /**
     * Initialize function
     */
    init() {
        this.removeUnused();
        this.calculateSizes();
        this.renderSlider();

        Event.listen(Event.EVENT.RESIZE, () => {
            this.updateOnResize();
        });
    }

    /**
     * Update on resize
     */
    updateOnResize() {
        this.$sliderContent.removeClass('-back')
            .removeClass('-right')
            .removeClass('-active')
            .removeClass('-left');

        this.removeUnused();
        this.calculateSizes();
        this.renderSlider();
    }
}
