const copy = require('copy');

copy('frontend/images/*', './cevi-themes/cevi/assets/images', function(err, file) {
    // copy all images to the cevi-theme.
});

copy('frontend/fonts/**', './cevi-themes/cevi/assets/fonts', function(err, file) {
    // copy all fonts to the cevi-theme.
});
