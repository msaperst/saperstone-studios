<?php


class File {

    private $files = array();
    private $location;

    function __construct($files) {
        // perform some basic checks on the input
        if (!isset ($files)) {
            throw new BadRequestException('File(s) are required');
        } elseif ($files == "") {
            throw new BadRequestException('File(s) can not be blank');
        } elseif (isset($files['error']) && $files['error'] != '0') {
            throw new BadRequestException($files['error']);
        } elseif (!isset($files['name'])) {
            throw new BadRequestException('File name is required');
        } elseif ($files['name'] == '') {
            throw new BadRequestException('File name can not be blank');
        } elseif (!isset($files['tmp_name'])) {
            throw new BadRequestException('File upload location is required');
        } elseif ($files['tmp_name'] == '') {
            throw new BadRequestException('File upload location can not be blank');
        }
        // extract out all of the files
        if (!is_array($files['name'])) {
            $files['name'] = self::validateFileName($files['name']);
            $this->files[] = $files;
        } else {
            for ($i = 0; $i < sizeof($files['name']); $i++) {
                $this->files[] = [
                    'name' => self::validateFileName($files['name'][$i]),
                    'tmp_name' => $files['tmp_name'][$i]
                ];
            }
        }
    }

    private static function validateFileName($name): string {
        if (!is_string($name) || str_contains($name, "\0")) {
            throw new BadRequestException('File name is not valid');
        }
        if (str_contains($name, '/') || str_contains($name, '\\')) {
            throw new BadRequestException('File name can not contain a path');
        }

        $fileName = basename($name);
        if ($fileName !== $name || $fileName === '.' || $fileName === '..') {
            throw new BadRequestException('File name is not valid');
        }
        return $fileName;
    }

    public static function validateImageExtension(string $filename): void {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'], true)) {
            throw new BadRequestException('Uploaded file type is not supported');
        }
    }

    private function removeUploadedFiles(): void {
        foreach ($this->files as $uploadedFile) {
            $uploadedPath = $this->location . $uploadedFile;
            if (is_file($uploadedPath)) {
                unlink($uploadedPath);
            }
        }
    }

    private function getValidatedImageSizes(): array {
        $sizes = [];
        foreach ($this->files as $index => $file) {
            try {
                self::validateImageExtension($file);
            } catch (BadRequestException $e) {
                $this->removeUploadedFiles();
                throw $e;
            }

            $path = $this->location . $file;
            $size = @getimagesize($path);
            if ($size === false) {
                $this->removeUploadedFiles();
                throw new BadRequestException('Uploaded file is not a valid image');
            }
            $sizes[$index] = $size;
        }
        return $sizes;
    }

    function getFiles() {
        return $this->files;
    }

    function upload($location) {
        $this->location = $location;
        if (!is_dir($location)) {
            $oldMask = umask(0);
            mkdir($location, 0775, true);
            umask($oldMask);
        }
        $files = array();
        foreach ($this->files as $file) {
            move_uploaded_file($file['tmp_name'], $location . $file['name']);
            $files[] = $file['name'];
        }
        $this->files = $files;
        return $this->files;
    }

    function resize($width, $height) {
        $sizes = $this->getValidatedImageSizes();
        foreach ($this->files as $index => $file) {
            $size = $sizes[$index];
            if ($size [0] < $width) { //verify the width
                unlink($this->location . $file);
                throw new BadRequestException("Image does not meet the minimum width requirements of {$width}px. Image is {$size[0]} x {$size[1]}");
            } elseif ($size [1] < $height) {//verify the height
                unlink($this->location . $file);
                throw new BadRequestException("Image does not meet the minimum height requirements of {$height}px. Image is {$size[0]} x {$size[1]}");
            } elseif ($width > 0 && $height > 0) {
                $imagePath = $this->location . $file;
                ImageProcessor::resize($imagePath, (int)$width, (int)$height);
                ImageProcessor::setDensity($imagePath, 72);
            }
        }
    }

    function addToDatabase($database, $parent, $parentId, $parentCol, $locationPrefix) {
        $sizes = $this->getValidatedImageSizes();
        $systemUser = User::fromSystem();
        $sql = new Sql();
        $databaseIdentifier = $sql->quoteIdentifier($database);
        $parentColumnIdentifier = $sql->quoteIdentifier($parentCol);
        $nextSeq = $sql->getRow("SELECT MAX(sequence) as next FROM $databaseIdentifier WHERE $parentColumnIdentifier = ?", [$parentId])['next'];
        if (is_numeric($nextSeq)) {
            $nextSeq++;
        } else {
            $nextSeq = 0;
        }
        foreach ($this->files as $index => $file) {
            $size = $sizes[$index];
            $width = $size[0];
            $height = $size[1];

            if (function_exists('exif_read_data')) {
                $exif = @exif_read_data($this->location . $file);

                if (!empty($exif['Orientation']) && in_array($exif['Orientation'], [5, 6, 7, 8])) {
                    // 90° or 270° rotation: swap width and height
                    [$width, $height] = [$height, $width];
                }
            }
            $sql->executeStatement("INSERT INTO $databaseIdentifier VALUES (NULL, ?, ?, ?, '', ?, ?, ?, 1)", [$parentId, $file, $nextSeq, $locationPrefix . $file, $width, $height]);

            if (!$systemUser->isAdmin() && $systemUser->isActive()) {
                $sql->executeStatement("INSERT INTO `user_logs` (`user`, `time`, `action`, `what`, `album`) VALUES (?, CURRENT_TIMESTAMP, 'Added Image', ?, ?)", [$systemUser->getId(), $nextSeq, $parentId]);
            }
            if ($parent == 'albums') {
                // update the image count
                $parentIdentifier = $sql->quoteIdentifier($parent);
                $sql->executeStatement("UPDATE $parentIdentifier SET `images` = images + 1 WHERE id = ?", [$parentId]);
            }
            $nextSeq++;
        }
        $sql->disconnect();
    }
}
