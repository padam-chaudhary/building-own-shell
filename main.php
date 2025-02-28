<?php
error_reporting(E_ALL);
require_once 'built_in.php';
require_once 'parsing.php';
require_once 'system.php';
$running = true;
while ($running) {
    fwrite(STDOUT, "$ ");
    $input = rtrim(fgets(STDIN)) ?: '';
    [$args, $redirection] = getArgs($input);
    $command = $args[0];
    if (!$command) {
        continue;
    }
    if (isBuiltIn((string)$command)) {
        ob_start();
        $output = match ($command) {
            'exit' => builtInExit((int)$args[1]),
            'echo' => builtInEcho(implode(' ', array_slice($args, 1))),
            'type' => builtInType((string)$args[1], (string)getenv('PATH')),
            'pwd' => builtInPwd(),
            'cd' => builtInCd($args[1]),
            default => fwrite(STDOUT, "$command: command not found" . PHP_EOL),
        };
        handleRedirection($output, $redirection);
        continue;
    }
    if ($executable = getExecutable($command, getenv('PATH'))) {
        $programName = basename($executable);
        $escapedArgs = implode(' ', array_map('escapeshellarg', array_slice($args, 1)));
        // Use "exec -a" to override argv[0] with the basename
        $cmd = sprintf('exec -a %s %s', escapeshellarg($programName), escapeshellarg($executable));
        if ($escapedArgs !== '') {
             $cmd .= ' ' . $escapedArgs;
        }
        $output = shell_exec($cmd);
        handleRedirection($output, $redirection);
        continue;
    }
    
    fwrite(STDOUT, "$command: command not found" . PHP_EOL);
}

function handleRedirection(string $output, ?string $redirection): void
{
    if ($redirection) {
        // Remove leading whitespace
        $redirection = ltrim($redirection);
        // Remove the redirection operator (handle both "1>" and ">")
        if (str_starts_with($redirection, '1>')) {
            $redirection = substr($redirection, 2);
        } elseif (str_starts_with($redirection, '>')) {
            $redirection = substr($redirection, 1);
        }
        // Trim any extra whitespace to get the filename
        $file = trim($redirection);
        file_put_contents($file, $output);
    } else {
        fwrite(STDOUT, $output);
    }
}
