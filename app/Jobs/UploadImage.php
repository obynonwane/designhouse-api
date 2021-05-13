<?php

namespace App\Jobs;

use App\Models\Design;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UploadImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $design;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Design $design)
    {
        $this->design = $design;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //get the disk where i intend to save image
        $disk = $this->design->disk;
        $filename = $this->design->image;

        //get the path in which the image is saved temporary on this system i.e which means getting the image
        $original_file = storage_path() . '/uploads/original/' . $filename;




        try {

            //below is Image Manipulation using Intervention Image

            //create a large Image and save to tmp disk i.e this is ressize the original image  
            //Maintain the aspect ratio in the constraint
            Image::make($original_file)
                ->fit(800, 600, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($large = storage_path('uploads/large/' . $filename));;



            //create a thumbnail
            Image::make($original_file)
                ->fit(250, 200, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($thumbnail = storage_path('uploads/thumbnail/' . $filename));

            //store Images to permanent disk -Below Code and  delete from temporary locaation
            //Store Original Image to disk,  put(accept destination and file you moving), set the original file to read and write from 
            //also check if it is (above operations) succesfull and delete the originaal file
            if (Storage::disk($disk)->put('uploads/designs/original/' . $filename, fopen($original_file, 'r+'))) {
                File::delete($original_file);
            };

            //large
            if (Storage::disk($disk)->put('uploads/designs/large/' . $filename, fopen($large, 'r+'))) {
                File::delete($large);
            };

            //thumbnail
            if (Storage::disk($disk)->put('uploads/designs/thumbnail/' . $filename, fopen($thumbnail, 'r+'))) {
                File::delete($thumbnail);
            };

            //update the database record with success flag
            $this->design->update([
                "upload_successful" => true
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
