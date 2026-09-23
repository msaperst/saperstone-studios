const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createAlbumDetailContext} = require('./helpers/album-detail-test-utils');

function prepareFilterContext(showFavoritesOnly) {
    const {context, environment, window} = createAlbumDetailContext();
    const card = environment.element('#album-grid .album-card');
    card.attr('data-image-id', '10');
    card.attr('data-favorite', '0');
    card.toggle = function (visible) {
        this.visible = Boolean(visible);
        return this;
    };

    const breadcrumbs = environment.element('.breadcrumb > li');
    breadcrumbs.last = () => breadcrumbs;
    environment.element('#album-title').text('Sample Album');

    const actions = environment.element('#actions');
    actions.before = function (value) {
        this.beforeValue = value;
        return this;
    };

    let loadCalls = 0;
    const album = Object.assign(Object.create(context.Album.prototype), {
        showFavoritesOnly,
        originalFavoritesBackup: null,
        viewingAdminUserFavorites: null,
        loadImages() {
            loadCalls += 1;
            return 0;
        }
    });
    window.album = album;

    return {
        album,
        card,
        context,
        environment,
        getLoadCalls: () => loadCalls
    };
}

test('album.js favorites filter hides non-favorite cards and updates filter controls', () => {
    const {album, card, environment, getLoadCalls} = prepareFilterContext(true);

    album.applyFilter();

    assert.equal(card.visible, false);
    assert.equal(environment.element('#favorite-btn').attr('title'), 'View all images from this album');
    assert.equal(environment.element('#favorite-btn em').hasClass('error'), false);
    assert.equal(environment.element('#favorite-count').visible, false);
    assert.equal(environment.element('#downloadable-all-btn').visible, false);
    assert.equal(environment.element('#downloadable-favorites-btn').visible, true);
    assert.equal(environment.element('#submit-favorites-btn').visible, true);
    assert.equal(environment.element('.breadcrumb > li').hasClass('active'), false);
    assert.ok(environment.element('#actions').beforeValue);
    assert.equal(getLoadCalls(), 1);
});

test('album.js clearing the favorites filter restores the original favorite snapshot', () => {
    const {album, card, environment, getLoadCalls} = prepareFilterContext(false);
    album.originalFavoritesBackup = {
        '10': '1'
    };
    album.viewingAdminUserFavorites = 'guest@example.com';

    album.applyFilter();

    assert.equal(card.visible, true);
    assert.equal(card.attr('data-favorite'), '1');
    assert.equal(card.hasClass('is-favorite'), true);
    assert.equal(album.originalFavoritesBackup, null);
    assert.equal(album.viewingAdminUserFavorites, null);
    assert.equal(environment.element('#favorite-btn').attr('title'), 'View favorite images from this album');
    assert.equal(environment.element('#favorite-btn em').hasClass('error'), true);
    assert.equal(environment.element('#favorite-count').visible, true);
    assert.equal(environment.element('#downloadable-all-btn').visible, true);
    assert.equal(environment.element('#downloadable-favorites-btn').visible, false);
    assert.equal(environment.element('#submit-favorites-btn').visible, false);
    assert.equal(environment.element('.breadcrumb > li').hasClass('active'), true);
    assert.equal(getLoadCalls(), 1);
});
