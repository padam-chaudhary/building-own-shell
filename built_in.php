<?php
const BUILT_IN = ['exit', 'echo', 'type', 'pwd', 'cd'];
function isBuiltIn(string $command): bool
{
    return in_array($command, BUILT_IN);
}
function builtInExit(int $code = 0): never
{
    exit($code);
}
function builtInEcho(string $value): string
{
    // The issue is here - we need to prevent the shell from removing backslashes
    // within double quotes when they're not escaping special characters
    
    // Don't use stripcslashes as it processes all escape sequences
    
    // Only process the value if we're not inside quotes
    // For double-quoted strings, preserve backslashes before characters that
    // shouldn't be escaped (like apostrophes)
    
    return $value . PHP_EOL;
}
function builtInType(string $command, string $path): string
{
    $builtInTemplate = '%s is a shell builtin' . PHP_EOL;
    $notFoundTemplate = '%s: not found' . PHP_EOL;
    switch ($command) {
        case 'exit':
        case 'echo':
        case 'type':
        case 'pwd':
            $output = sprintf($builtInTemplate, $command);
            break;
        default:
            if ($executable = getExecutable($command, $path)) {
                $output = $executable . PHP_EOL;
            } else {
                $output = sprintf($notFoundTemplate, $command);
            }
    }
    return $output;
}
function builtInPwd(): string
{
    return getcwd() . PHP_EOL;
}
function builtInCd(string $path): string
{
    $noSuchPathTemplate = "cd: %s: No such file or directory" . PHP_EOL;
    $isNotDirTemplate = "cd: %s: Not a directory" . PHP_EOL;
    if ($path === '~') {
        $home = getenv('HOME');
        if ($home === false || is_array($home)) {
            $home = '/';
        }
        $path = (string)$home;
    }
    if (!file_exists($path)) {
        return sprintf($noSuchPathTemplate, $path);
    }
    if (!is_dir($path)) {
        return sprintf($isNotDirTemplate, $path);
    }
    chdir($path);
    return '';
}