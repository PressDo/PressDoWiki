<?php
namespace Pressdo\app\Helpers\Uploaders;

use PressDo\app\Helpers\DefaultConfig;

class Local
{
    public function __construct()
    {
        // empty
    }

    public function execute(array $options)
    {
        try {
            move_uploaded_file($options['file'], 'files/'.$options['path']);
        } catch (\Exception $exception) {
            echo "Failed to upload with error: " . $exception->getMessage();
            exit("Please fix error with file upload before continuing.");
        }
    }
}