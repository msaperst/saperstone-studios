const assert = require('node:assert/strict');
const {test} = require('node:test');
const {createAlbumContext} = require('./helpers/album-test-utils');

const scriptPath = 'public/js/album-collections-list.js';

test('child rows keep names as text and do not expose organization controls to clients', () => {
    const {context} = createAlbumContext(scriptPath);
    const item = context.collectionChildItem({
        id: 88, name: '<img src=x onerror=alert(1)>', imageCount: 12
    }, false);
    assert.equal(item.attr('data-child-id'), '88');
    const link = item.appended.flatMap(cell => cell.appended).find(entry => entry.tagName === 'a');
    assert.equal(link.attr('href'), '/user/album.php?album=88');
    assert.equal(link.text(), '<img src=x onerror=alert(1)>');
    assert.equal(item.appended.flatMap(cell => cell.appended).some(entry => entry.tagName === 'button'), false);
});

test('authorized child rows have drag handles and accessible keyboard controls', () => {
    const {context} = createAlbumContext(scriptPath);
    const item = context.collectionChildItem({
        id: 88, name: 'Reception', imageCount: 12
    }, true);
    const buttons = item.appended.flatMap(cell => cell.appended).filter(entry => entry.tagName === 'button');
    assert.equal(buttons.length, 2);
    assert.ok(buttons.some(entry => entry.attr('aria-label') === 'Move Reception up'));
    assert.ok(buttons.some(entry => entry.attr('aria-label') === 'Move Reception down'));
    assert.ok(!buttons.some(entry => /Detach Reception/.test(entry.attr('aria-label') || '')));
});

test('failed order persistence surfaces an error and reloads the authoritative order', () => {
    const {context, environment} = createAlbumContext(scriptPath);
    let reloaded = false;
    context.collectionOrderIds = () => [23, 21];
    context.renderCollectionChildren = () => { reloaded = true; };
    environment.queuePost('/api/reorder-collection.php', {
        type: 'failure', xhr: {responseText: 'Order is invalid'}
    });
    context.saveCollectionOrder(42, environment.element('__ordered_list__'), environment.element('__holder__'));
    assert.deepEqual(JSON.parse(JSON.stringify(environment.calls.post[0])), {
        url: '/api/reorder-collection.php',
        data: {collection: 42, order: '[23,21]'}
    });
    assert.equal(environment.alerts.at(-1), 'Order is invalid');
    assert.equal(reloaded, true);
});

test('expanded child rows show the same Ready, Missing and N/A thumbnail statuses as standalone albums', () => {
    const {context} = createAlbumContext(scriptPath);
    for (const [count, complete, expected] of [
        [7, true, 'Ready'],
        [131, false, 'Missing'],
        [0, false, 'N/A']
    ]) {
        const item = context.collectionChildItem({
            id: 88, name: 'Reception', imageCount: count, thumbsCreated: complete
        }, true);
        const status = item.appended.flatMap(cell => cell.appended).find(entry =>
            entry && entry.hasClass && entry.hasClass('collection-thumbnail-status'));
        assert.ok(status, 'Child row is missing thumbnail status');
        assert.equal(status.text(), expected);
    }
});

test('child thumbnail status is informational and does not replace per-album upload actions', () => {
    const {context} = createAlbumContext(scriptPath);
    const item = context.collectionChildItem({
        id: 88, name: 'Reception', imageCount: 7, thumbsCreated: false
    }, true);
    const link = item.appended.find(entry => entry.tagName === 'a');
    assert.equal(link.attr('href'), '/user/album.php?album=88');
    assert.equal(item.appended.flatMap(cell => cell.appended).filter(entry => entry.tagName === 'button').length, 2);
});

test('compact child details show photos, thumbnail state, last access and code in separate cells', () => {
    const {context} = createAlbumContext(scriptPath);
    const row = context.collectionChildItem({
        id: 88, name: 'Reception', description: 'The evening',
        date: '2026-10-12', imageCount: 12,
        thumbsCreated: true, lastAccessed: '2026-10-15 12:34:56', code: 'RECEPTION88'
    }, true);
    assert.equal(row.tagName, 'tr');
    assert.equal(row.attr('data-child-id'), '88');
    const cells = row.appended.filter(item => item.tagName === 'td');
    assert.equal(cells.length, 8);
    assert.ok(cells.some(item => item.text().includes('RECEPTION88')));
    assert.ok(cells.some(item => item.text().includes('2026-10-15')));
    assert.ok(cells.some(item => item.text().includes('2026-10-12')));
});

test('admin child rows provide a direct edit control; non-admin rows do not', () => {
    const {context} = createAlbumContext(scriptPath);
    const child = {id: 88, name: 'Reception', imageCount: 12};
    const adminRow = context.collectionChildItem(child, true);
    const controls = adminRow.appended.flatMap(cell => cell.appended);
    assert.ok(controls.some(item => item.hasClass && item.hasClass('collection-child-edit-album')));
    const nonAdmin = context.collectionChildItem(child, false);
    assert.ok(!nonAdmin.appended.flatMap(cell => cell.appended)
        .some(item => item.hasClass && item.hasClass('collection-child-edit-album')));
});
