<?php

namespace App\Http\Controllers\Admin\System;

use App\Models\Module;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\DB;

class AddonController extends Controller
{
    public function __construct(){
        if (is_dir('Modules\Gateways\Traits') && trait_exists('Modules\Gateways\Traits\SmsGateway')) {
            $this->extendWithSmsGatewayTrait();
        }
    }

    private function extendWithSmsGatewayTrait()
    {
        $extendedControllerClass = $this->generateExtendedControllerClass();
        eval($extendedControllerClass);
    }

    private function generateExtendedControllerClass()
    {
        $baseControllerClass = get_class($this);
        $traitClassName = 'Modules\Gateways\Traits\SmsGateway';

        $extendedControllerClass = "
            class ExtendedController extends $baseControllerClass {
                use $traitClassName;
            }
        ";

        return $extendedControllerClass;
    }

    public function index()
    {
        return view('admin-views.system.addon.index');
    }

    public function publish(Request $request)
    {
        $validation = [
            'name' => 'required'
        ];
        $request->validate($validation);

        $publish_data = [
            'name' => $request['name'],
            'is_published' => 1
        ];
        DB::table('addon_settings')->updateOrInsert(['key_name' => $request['name']], $publish_data);

        Toastr::success(translate('messages.addon_published_successfully'));
        return back();
    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file_upload' => 'required|mimes:zip'
        ]);

        if ($validator->errors()->count() > 0) {
            $error = Helpers::error_processor($validator);
            return response()->json(['status' => 'error', 'message' => $error[0]['message']]);
        }

        $file = $request->file('file_upload');
        $filename = $file->getClientOriginalName();
        $tempPath = $file->storeAs('temp', $filename);
        $zip = new \ZipArchive();

        if ($zip->open(storage_path('app/' . $tempPath)) === TRUE) {
            // Extract the contents to a directory
            $extractPath = base_path('Modules/');
            $zip->extractTo($extractPath);
            $zip->close();
            if(File::exists($extractPath.'/'.explode('.', $filename)[0].'/Addon/info.php')){
                File::chmod($extractPath.'/'.explode('.', $filename)[0].'/Addon', 0777);
                Toastr::success(translate('file_upload_successfully!'));
                $status = 'success';
                $message = translate('file_upload_successfully!');
            }else{
                File::deleteDirectory($extractPath.'/'.explode('.', $filename)[0]);
                $status = 'error';
                $message = translate('invalid_file!');
            }
        }else{
            $status = 'error';
            $message = translate('file_upload_fail!');
        }

        Storage::delete($tempPath);

        return response()->json([
            'status' => $status,
            'message'=> $message
        ]);
    }

    public function delete_theme(Request $request){
        if (env('APP_MODE') == 'demo') {
            Toastr::info(translate('messages.update_option_is_disable_for_demo'));
            return back();
        }
        $path = $request->path;

        $full_path = base_path($path);

        if(File::deleteDirectory($full_path)){
            return response()->json([
                'status' => 'success',
                'message'=> translate('file_delete_successfully')
            ]);
        }else{
            return response()->json([
                'status' => 'error',
                'message'=> translate('file_delete_fail')
            ]);
        }

    }

    //helper functions
    function getDirectories(string $path): array
    {
        $directories = [];
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item == '..' || $item == '.')
                continue;
            if (is_dir($path . '/' . $item))
                $directories[] = $item;
        }
        return $directories;
    }

    private function rentalPublish(int|bool $is_published): bool
    {
        try {
            $module = Module::firstOrNew(
                ['module_type' => 'rental'],
                ['module_name' => 'Rental']
            );

            if ($is_published) {
                Artisan::call('migrate', ['--force' => true]);
                $module->status = 1;
            } else {
                $module->status = 0;
            }

            $module->save();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
