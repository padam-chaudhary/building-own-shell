<?php
function getExecutable(string $command, string $path): string
{
    // Fallback to default paths if $path is empty
    $dirs = explode(PATH_SEPARATOR, $path ?: '/bin:/usr/bin:/usr/local/bin');
    $executable = '';
    foreach ($dirs as $dir) {
        $file = $dir . DIRECTORY_SEPARATOR . $command;
        if (is_executable($file)) {
            $executable = $file;
            break;
        }
    }
    return $executable;
}
?>