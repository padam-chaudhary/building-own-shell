<?php
/** @noinspection t */
function getArgs(string $input): array
{
    $arg = '';
    $args = [];
    $redirection = '';
    $state = [
        'inSingleQuote' => false,
        'inDoubleQuote' => false,
        'hasBackslash' => false,
    ];
    
    for ($i = 0, $len = strlen($input); $i < $len; $i++) {
        $char = $input[$i];
        
        // Handle backslash
        if ($char === '\\' && !$state['inSingleQuote'] && !$state['hasBackslash']) {
            $state['hasBackslash'] = true;
            continue;
        }
        
        // Process character after backslash
        if ($state['hasBackslash']) {
            // Inside double quotes, only certain characters are escaped
            if ($state['inDoubleQuote']) {
                if (in_array($char, ['\\', '$', '"', "\n"])) {
                    $arg .= $char;  // Escaped character inside double quotes
                } else {
                    // For other characters, keep both the backslash and character
                    $arg .= '\\' . $char;
                }
            } else {
                // Outside quotes, all backslashes escape the next character
                $arg .= $char;
            }
            $state['hasBackslash'] = false;
            continue;
        }
        
        // Handle quote characters
        if ($char === '\'' && !$state['inDoubleQuote']) {
            $state['inSingleQuote'] = !$state['inSingleQuote'];
            continue;
        }
        
        if ($char === '"' && !$state['inSingleQuote']) {
            $state['inDoubleQuote'] = !$state['inDoubleQuote'];
            continue;
        }
        
        // Handle spaces (only if not in quotes)
        if ($char === ' ' && !$state['inSingleQuote'] && !$state['inDoubleQuote']) {
            if ($arg !== '') {
                $args[] = $arg;
                $arg = '';
            }
            continue;
        }
        
        // Handle redirection
        if (!$state['inSingleQuote'] && !$state['inDoubleQuote']) {
            // Handle 1> redirection
            if ($char === '1' && ($i + 1) < $len && $input[$i + 1] === '>') {
                if ($arg !== '') {
                    $args[] = $arg;
                }
                $redirection = substr($input, $i);
                break;
            }
            // Handle > redirection
            else if ($char === '>') {
                if ($arg !== '') {
                    $args[] = $arg;
                }
                $redirection = substr($input, $i);
                break;
            }
        }
        
        // Add character to current argument
        $arg .= $char;
    }
    
    // Add the last argument if it exists
    if ($arg !== '') {
        $args[] = $arg;
    }
    
    // Ensure at least two elements in args array
    $args += [0 => null, 1 => null];
    
    return [$args, $redirection];
}