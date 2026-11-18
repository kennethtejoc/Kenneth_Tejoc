<?php
/**
 * Escape a value before rendering it in HTML, including quotes and invalid UTF-8.
 */
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
