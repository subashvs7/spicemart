<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Output — patches CI3's PHP 8.1 incompatibility.
 *
 * PHP 8.1 deprecated passing null as the $subject argument to str_replace().
 * CI3's Output::_display() can receive null in certain flows, triggering the
 * deprecation notice. Casting to string before calling the parent is safe:
 * CI3 treats an empty string as "use the internal buffer", so behaviour is
 * identical to passing '' when no output has been supplied directly.
 */
class MY_Output extends CI_Output
{
    public function _display($output = '')
    {
        parent::_display((string) $output);
    }
}
