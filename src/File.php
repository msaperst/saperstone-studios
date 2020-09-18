<?php


class File {

    private $files = array();
    private $location;

    function __construct($files) {
        // perform some basic checks on the input
        if (!isset ($files)) {
            throw new Exception ('File(s) are required');
        } elseif ($files == "") {
            throw new Exception ('File(s) can not be blank');
        } elseif (isset($files['error']) && $files['error'] != '0') {
            throw new Exception($files['error']);
        } elseif (!isset($files['name'])) {
            throw new Exception('File name is required');
        } elseif ($files['name'] == '') {
            throw new Exception('File name can not be blank');
        } elseif (!isset($files['tmp_name'])) {
            throw new Exception('File upload location is required');
        } elseif ($files['tmp_name'] == '') {
            throw new Exception('File upload location can not be blank');
        }
        // extract out all of the files
        if (!is_array($files['name'])) {
            $this->files[] = $files;
        } else {
            for ($i = 0; $i < sizeof($files['name']); $i++) {
                $this->files[] = [
                    'name' => $files['name'][$i],
                    'tmp_name' => $files['tmp_name'][$i]
                ];
            }
        }
    }

    function getFiles() {
        return $this->files;
    }

    function upload($location) {
        $this->location = $location;
        if (!is_dir($location)) {
            mkdir($location, 0755, true);
        }
        $files = array();
        foreach ($this->files as $file) {
            $x = move_uploaded_file($file['tmp_name'], $location . $file['name']);
            $files[] = $file['name'];
        }
        $this->files = $files;
        return $this->files;
    }

    function resize($width, $height) {
        foreach ($this->files as $file) {
            $size = getimagesize($this->location . $file);
            if ($size [0] < $width) { //verify the width
                unlink($this->location . $file);
                throw new Exception("Image does not meet the minimum width requirements of {$width}px. Image is {$size[0]} x {$size[1]}");
            } elseif ($size [1] < $height) {//verify the height
                unlink($this->location . $file);
                throw new Exception("Image does not meet the minimum height requirements of {$height}px. Image is {$size[0]} x {$size[1]}");
            } else {
                system("mogrify -resize {$width}x{$height} \"{$this->location}{$file}\"");
                system("mogrify -density 72 \"{$this->location}{$file}\"");
            }
        }
    }

    function addToDatabase($database, $parent, $parentId, $parentCol, $locationPrefix) {
        $systemUser = User::fromSystem();
        $sql = new Sql();
        $nextSeq = $sql->getRow("SELECT MAX(sequence) as next FROM $database WHERE $parentCol = '$parentId';")['next'];
        if (is_numeric($nextSeq)) {
            $nextSeq++;
        } else {
            $nextSeq = 0;
        }
        foreach ($this->files as $file) {
            $size = getimagesize($this->location . $file);
            $sql->executeStatement("INSERT INTO `$database` VALUES (NULL, '$parentId', '$file', '$nextSeq', '', '{$locationPrefix}$file', '{$size[0]}', '{$size[1]}', 1);");
            if (!$systemUser->isAdmin()) {
                sleep(1); //TODO - this is a bug in our keys, should fix this
                $sql->executeStatement("INSERT INTO `user_logs` VALUES ( {$systemUser->getId()}, CURRENT_TIMESTAMP, 'Added Image', $nextSeq, $parentId );");
            }
            // update the image count
            $sql->executeStatement("UPDATE `$parent` SET `images` = images + 1 WHERE id='$parentId';");
            $nextSeq++;
        }
        $sql->disconnect();
    }
}