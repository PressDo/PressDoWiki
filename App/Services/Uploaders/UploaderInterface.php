<?php
namespace Pressdo\App\Services\Uploaders;

interface UploaderInterface
{
    public function __construct();

    public function execute(array $options): void;
}