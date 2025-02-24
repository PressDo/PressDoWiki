<?php
namespace Pressdo\app\Helpers\Uploaders;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\Credentials\Credentials;
use PressDo\app\Helpers\DefaultConfig;

class Local
{
    public $S3;

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