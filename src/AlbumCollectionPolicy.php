<?php

/**
 * Shared authorization rules for organizational album collections.
 *
 * This class deliberately has no storage or session dependencies so the
 * security contract can be exercised exhaustively in unit tests.
 */
final class AlbumCollectionPolicy {
    public static function canSetCode(bool $isAdmin): bool {
        return $isAdmin;
    }

    public static function canEdit(bool $isAdmin, bool $isUploader, int $actorId, int $ownerId): bool {
        return $isAdmin || ($isUploader && $actorId === $ownerId);
    }

    public static function canChangeMembership(
        bool $isAdmin,
        bool $isUploader,
        int $actorId,
        int $collectionOwner,
        int $albumOwner,
        bool $sourceShared,
        bool $targetShared
    ): bool {
        return $isAdmin || (
            $isUploader && $actorId === $collectionOwner && $actorId === $albumOwner
            && !$sourceShared && !$targetShared
        );
    }

    public static function canDelete(
        bool $isAdmin,
        bool $isUploader,
        int $actorId,
        int $ownerId,
        bool $shared
    ): bool {
        return $isAdmin || ($isUploader && $actorId === $ownerId && !$shared);
    }
}
