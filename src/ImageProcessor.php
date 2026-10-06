<?php

class ImageProcessor {

    /**
     * Run ImageMagick without invoking a shell.
     *
     * Passing the command as an argument array prevents shell metacharacters in
     * image paths or operation values from being interpreted as commands.
     */
    public static function mogrify(string $imagePath, array $arguments): void {
        $command = array_merge(['mogrify'], $arguments, [$imagePath]);
        $process = proc_open(
            $command,
            [
                0 => ['file', '/dev/null', 'r'],
                1 => ['file', '/dev/null', 'w'],
                2 => ['file', '/dev/null', 'w'],
            ],
            $pipes
        );

        if (is_resource($process)) {
            proc_close($process);
        }
    }
}
