<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\Filesystem;
use League\Flysystem\Config;
use League\Flysystem\FilesystemException;

class UploadToS3 extends Command
{
    protected $signature = 'command:batch_upload_file';

    protected $description = 'Batch Upload Local File To S3';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->batchMultiUploadFileByS3();
    }

    public function batchMultiUploadFileByS3()
    {
        $time_start = microtime(true);
        $files = Storage::disk('public')->allFiles();
        foreach ($files as $file) {
            if($file =='test.zip'){
                log::info($file);
            }elseif($file = []){
                log::info('khong ton tai');
            }    
        }
        
        die;
        try {
            $files = Storage::disk('public')->allFiles();
            foreach ($files as $file) {
                $this->uploadFileToS3($file);
            }
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
        }

        echo 'Total execution batch multi upload: ' . (microtime(true) - $time_start);
    }
    public function uploadFileToS3($fileName)
    {
        try {
            $fileContent = Storage::disk('public')->get($fileName);
    
            Storage::disk('s3')->put($fileName, $fileContent);
    
         
            if (Storage::disk('s3')->exists($fileName)) {
                Storage::disk('public')->delete($fileName);
                $this->info("File uploaded successfully and deleted locally: $fileName");
            } else {
                $this->error("Upload failed for file $fileName: File not found on S3");
            }
        } catch (\Exception $ex) {
            $this->error("Upload failed for file $fileName: " . $ex->getMessage());
        }
    }
    
}
