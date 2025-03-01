<?php
namespace Pressdo\App\Services\Uploaders;

//use PressDo\App\Services\DefaultConfig;

class Local implements UploaderInterface
{
    public function __construct()
    {
        // empty
    }

    public function execute(array $options): void
    {
        try {
            move_uploaded_file($options['file'], 'files/'.$options['path']);
        } catch (\Exception $exception) {
            echo "Failed to upload with error: " . $exception->getMessage();
            exit("Please fix error with file upload before continuing.");
        }
    }
}