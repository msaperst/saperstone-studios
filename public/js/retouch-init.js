$(document).ready(function () {
    const config = $('#retouch-config');
    if (!config.length) {
        return;
    }

    let parsedImages = [];
    try {
        const rawImages = JSON.parse(config.attr('data-images') || '[]');
        if (Array.isArray(rawImages)) {
            parsedImages = rawImages
                .filter(function (image) {
                    return image && typeof image === 'object';
                })
                .map(function (image) {
                    return {
                        width: Number(image.width),
                        height: Number(image.height),
                        orig: typeof image.orig === 'string' ? image.orig : '',
                        edit: typeof image.edit === 'string' ? image.edit : '',
                        thumb: typeof image.thumb === 'string' ? image.thumb : '',
                        text: typeof image.text === 'string' ? image.text : ''
                    };
                })
                .filter(function (image) {
                    return Number.isFinite(image.width) && image.width > 0 &&
                        Number.isFinite(image.height) && image.height > 0;
                });
        }
    } catch (e) {
        parsedImages = [];
    }

    window.retouch = new Retouch($('#holder'), parsedImages, config.attr('data-instructions') !== 'false');
});
