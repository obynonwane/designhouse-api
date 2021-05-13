<?php

namespace App\Http\Controllers\Designs;

use App\Jobs\UploadImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UploadController extends Controller
{
    //
    public function upload(Request $request)
    {
        //validate the request
        $this->validate($request, [
            "image" => ['required', "mimes:jpeg,gif,bmp,png,jpg", "max:2048"]
        ]);

        //Get Image from the rquest
        $image = $request->file('image');

        //get original pathname of the image been uploaded 
        $image_path = $image->getPathName();

        //get original filename and replace any spaces with underscore
        //attach time stamp to file name incase we have file with similaar name
        //convert file original name to lower cases
        $filename = time() . "_" . preg_replace('/\$+/', '_', strtolower($image->getClientOriginalName()));

        //move image to temporary location in the tmp disk created
        $tmp = $image->storeAs('uploads/original', $filename, 'tmp');

        //create a database recored for the design:makaing use of the relationships that exist between user and design
        $design = auth()->user()->designs()->create([
            'user_id' => auth()->id(),
            'image' => $filename,
            'disk' => config('site.upload_disk')
        ]);

        //dispatch a job to hnadle Image Manipulation 
        $this->dispatch(new UploadImage($design));

        return response()->json($design, 200);
    }
}
