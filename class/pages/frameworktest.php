<?php
/**
 * A class that contains code to handle any requests for  /testingtesting/
 *
 * @author Your Name <Your@email.org>
 * @copyright year You
 * @package Framework
 * @subpackage UserPages
 */
    namespace Pages;

    use \Support\Context as Context;
/**
 * Support /testingtesting/
 */
    class frameworktest extends \Framework\Siteaction
    {
/**
 * Handle testingtesting operations
 *
 * @param Context   $context    The context object for the site
 *
 * @return string|array   A template name
 */
        public function handle(Context $context)
        {
            $projects = array("one","two");
            $context->local()->addval('project',$projects);
            return '@content/frameworktest.twig';
        }
    }
?>