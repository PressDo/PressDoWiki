<?php
namespace Pressdo\app\Helpers\Uploaders;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\Credentials\Credentials;
use PressDo\app\Helpers\DefaultConfig;

class S3
{
    public $S3;

    public function __construct()
    {
        $option = [
            'version'  => DefaultConfig::get('storage.version'),
            'region'   => DefaultConfig::get('storage.region'),
            'credentials' => new Credentials(DefaultConfig::get('storage.key'), DefaultConfig::get('storage.secret'))
        ];
        
        if (!empty(DefaultConfig::get('storage.endpoint')))
            $option['endpoint'] = DefaultConfig::get('storage.endpoint');

        $this->S3 = new S3Client($option);
    }

    public function execute(array $options)
    {
        try {
            $this->S3->putObject([
                'Bucket' => DefaultConfig::get('storage.bucket'),
                'Key' => $options['path'],
                'SourceFile' => $options['file'],
                'ACL' => 'public-read'
            ]);
        } catch (\Exception $exception) {
            echo "Failed to upload with error: " . $exception->getMessage();
            exit("Please fix error with file upload before continuing.");
        }
    }
}