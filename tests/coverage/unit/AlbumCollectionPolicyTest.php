<?php

namespace coverage\unit;

use AlbumCollectionPolicy;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/src/autoloader.php';

class AlbumCollectionPolicyTest extends TestCase {
    public function testOnlyAnAdministratorCanSetAccessCodes(): void {
        self::assertTrue(AlbumCollectionPolicy::canSetCode(true));
        self::assertFalse(AlbumCollectionPolicy::canSetCode(false));
    }

    public function testUploaderMayEditOnlyOwnedCollections(): void {
        self::assertTrue(AlbumCollectionPolicy::canEdit(false, true, 4, 4));
        self::assertFalse(AlbumCollectionPolicy::canEdit(false, true, 4, 5));
        self::assertFalse(AlbumCollectionPolicy::canEdit(false, false, 4, 4));
        self::assertTrue(AlbumCollectionPolicy::canEdit(true, false, 4, 5));
    }

    public function testUploaderCannotChangeMembershipOfSharedCollections(): void {
        self::assertTrue(AlbumCollectionPolicy::canChangeMembership(false, true, 4, 4, 4, false, false));
        self::assertFalse(AlbumCollectionPolicy::canChangeMembership(false, true, 4, 4, 4, true, false));
        self::assertFalse(AlbumCollectionPolicy::canChangeMembership(false, true, 4, 4, 4, false, true));
        self::assertFalse(AlbumCollectionPolicy::canChangeMembership(false, true, 4, 5, 4, false, false));
        self::assertFalse(AlbumCollectionPolicy::canChangeMembership(false, true, 4, 4, 5, false, false));
        self::assertFalse(AlbumCollectionPolicy::canChangeMembership(false, false, 4, 4, 4, false, false));
        self::assertTrue(AlbumCollectionPolicy::canChangeMembership(true, false, 1, 5, 4, true, true));
    }

    public function testUploaderCannotDeleteSharedOrUnownedCollections(): void {
        self::assertTrue(AlbumCollectionPolicy::canDelete(false, true, 4, 4, false));
        self::assertFalse(AlbumCollectionPolicy::canDelete(false, true, 4, 4, true));
        self::assertFalse(AlbumCollectionPolicy::canDelete(false, true, 4, 5, false));
        self::assertFalse(AlbumCollectionPolicy::canDelete(false, false, 4, 4, false));
        self::assertTrue(AlbumCollectionPolicy::canDelete(true, false, 1, 4, true));
    }
}
