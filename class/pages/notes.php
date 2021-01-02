<?php
/**
 * A class that contains code to handle any requests for  /notes/
 *
 * @author Your Name <Your@email.org>
 * @copyright year You
 * @package Framework
 * @subpackage UserPages
 */
    namespace Pages;

    use \Support\Context as Context;
/**
 * Support /notes/
 */
    class Notes extends \Framework\Siteaction
    {
/**
 * Handle notes operations
 *
 * @param Context   $context    The context object for the site
 *
 * @return string|array   A template name
 */
        public function handle(Context $context)
        {
            return '@content/notes.twig';
        }
    }
?>