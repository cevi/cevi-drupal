/* global Image */
import $ from 'jquery';
import Event from '../../Helper/Event';

/**
 * Create a slider.
 */
export default class Slider {
    constructor({
        dataSlideshowName = 'slideshow',
        dataSliderName = 'slider',
        intervalTime = 4000
    } = {}) {
        this.$slideshowElements = $(`[data-${dataSlideshowName}]`);
        this.$visibleSlideshowElements = [];
        this.slideShows = null;
        this.dataSlider = dataSliderName;
        this.intervalTime = intervalTime;
        this.sliderInterval = null;
        this.SIZE_FULL = 'full';
        this.SIZE_DEFAULT = 'default';
        return this;
    }

    prepareSliderContent() {
        // First: generate empty arrays for all slideshows (shows and noshows).
        if (!this.slideShows) {
            this.slideShows = {};

            $.each(this.$slideshowElements, (index, element) => {
                this.slideShows[index] = {};
                $(element).data('slide-show-id', index);
            });
        }

        $.each(this.$visibleSlideshowElements, (i, slideshow) => {
            const self = this;
            const $slideshow = $(slideshow);
            const $sliders = $slideshow.find(`[data-${this.dataSlider}]`);
            const id = $slideshow.data('slide-show-id');
            const slideshowSize = $slideshow.hasClass('-full') ? self.SIZE_FULL : self.SIZE_DEFAULT;

            if (this.slideShows[id] === {} || !this.slideShows[id].loaded) {
                // Create slideshow-Object
                this.slideShows[id] = {
                    loaded: false,
                    sliders: $sliders,
                    'image-urls': {},
                    images: {},
                    slideshow: $slideshow,
                    index: 0,
                    interval: null,
                    size: slideshowSize,
                    imageProportions: []
                };

                $.each($sliders, (ii, element) => {
                    const $element = $(element);
                    const backgroundImage = $element.data('image-url');
                    this.slideShows[id]['image-urls'][ii] = backgroundImage;

                    const imageObject = new Image();
                    imageObject.onload = (event) => {
                        let backgroundImageSrc = '';

                        // Chrome
                        if (event.path) {
                            backgroundImageSrc = event.path[0].src;
                        }

                        // Firefox
                        if (event.target) {
                            backgroundImageSrc = event.target.currentSrc;
                        }

                        $(self.slideShows[id].sliders[ii]).css('background-image', `url(${backgroundImageSrc}`);
                        self.slideShows[id].images[ii] = backgroundImageSrc;

                        // height  -  100
                        // width   -    x
                        self.slideShows[id].imageProportions.push((imageObject.naturalWidth * 100) / imageObject.naturalHeight);

                        if (self.slideShows[id].sliders.length === Object.keys(self.slideShows[id].images).length) {
                            self.slideShows[id].loaded = true;
                            if (self.slideShows[id].size === self.SIZE_DEFAULT) {
                                self.setSlideshowHeight(self.slideShows[id]);
                            }
                            self.renderSlideshow(self.slideShows[id]);
                        }
                    };

                    imageObject.src = backgroundImage;
                });
            } else {
                self.renderSlideshow(self.slideShows[id]);
            }
        });
    }

    /**
     * Render the slider.
     *
     * Turns one elements at the time into an active element.
     *
     * @param slideshowParam
     */
    renderSlideshow(slideshowParam) {
        const slideshow = slideshowParam;
        slideshow.slideshow.find('.sliderplaceholder').addClass('-hide');

        // Stop the slider firstly.
        if (slideshow.interval) {
            clearInterval(slideshow.interval);
        }

        const elementsLength = slideshow.sliders.length;

        function getActiveElement() {
            if (slideshow.index >= elementsLength - 1) {
                slideshow.index = 0;
                return 0;
            }
            slideshow.index += 1;
            return slideshow.index;
        }

        function slide() {
            const i = getActiveElement();
            slideshow.sliders[i].classList.remove('-activated');
            slideshow.sliders[i].offsetHeight; // eslint-disable-line
            slideshow.sliders[i].classList.add('-activated');
        }

        slide();

        slideshow.interval = setInterval(() => {
            slide();
        }, this.intervalTime);
    }

    /**
     * Set the height of the slideshow based on the imageProportions.
     * Takes the biggest image and set all images to it's height.
     *
     * @param slideshowParam
     */
    setSlideshowHeight(slideshowParam) { // eslint-disable-line class-methods-use-this
        const slideshow = slideshowParam;
        let minProportion = 0;

        $.each(slideshow.imageProportions, (index, proportion) => {
            if (index === 0) {
                minProportion = proportion;
            }

            if (minProportion > proportion) {
                minProportion = proportion;
            }
        });

        const width = $(slideshow.sliders[0]).width();

        // x      -             100
        // width  -  maxProportions
        const height = (width * 100) / minProportion;

        slideshow.slideshow.height(`${height}px`);
    }

    cleanFirstAndStart() {
        $.each(this.$slideshowElements, (index, element) => {
            if ($(element).is(':visible')) {
                this.$visibleSlideshowElements.push($(element));
            }
        });

        this.prepareSliderContent();
    }

    /**
     * Initialize function
     */
    init() {
        this.cleanFirstAndStart();

        Event.listen(Event.EVENT.RESIZE, () => {
            this.updateOnResize();
        });
    }

    /**
     * Update on resize
     */
    updateOnResize() {
        this.cleanFirstAndStart();
    }
}
