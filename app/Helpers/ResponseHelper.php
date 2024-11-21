<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Exception;
use App\Http\Controllers\Controller;
use Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\SupabaseService;

class ResponseHelper
{
    /**
     * Send a success response.
     *
     * @param string $message
     * @param mixed $data
     * @return JsonResponse
     */
    public static function success(string $message,$code=200, $data = null, $data_name = null ): JsonResponse
    {   
        $response = [
            'status' => true,
            'message' => $message,
            'code' => $code,
        ];
        if(isset($data_name) && !empty($data_name)){
            $response[$data_name] = $data;
        }else{
            $response['data'] = $data;
        }
        return response()->json($response);
    }

    /**
     * Send an error response.
     *
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    public static function error(string $message, int $code = 400): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'code' => $code,
        ], $code);
    }

    function auth_user()
    {
        $userId = session('user_id');
        return $userId ? User::find($userId) : null;
    }

    /**
     * Add or Edit a record in the database.
     *
     * @param Model $model
     * @param array $data
     * @param int|null $id
     * @return Model
     */
    public static function addOrEdit(Model $model, array $data, array $conditions = [])
    {
        try {
            // Find the record based on the provided conditions or create a new one if not found
            $record = $model->firstOrNew($conditions);

            // Fill the record with new data
            $record->fill($data);

            // Optionally, validate the data before saving
            // Example: $record->validate();

            // Save the record (this will either create or update depending on the record state)
            $record->save();

            return $record;

        } catch (Exception $e) {
            // Handle exceptions and log the error message
            // Log::error($e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    public static function imageUploadNew_old(Request $request, $name, $path = false, $isPDF = false)
    {
        try {

            $cNs = new Controller();
            if ($isPDF) {
                // $cNs->validate($request, [
                //     "$name" => 'required|mimes:pdf|max:5120'
                // ]);
                $validator = Validator::make($request->all(), [
                    "$name" => 'required',
                    // "$name.*" => 'mimes:pdf,jpeg,png,jpg|max:5120',
                ], [
                    "$name" . '.required' => "Please upload " . $name . " file.",
                    // "$name" . '.mimes' => "Please upload " . $name . " an only a pdf,jpeg,png,jpg file.",
                    // "$name" . '.max' => "Please upload " . $name . " a PDF file smaller than 5 MB.",
                ]);
            } else {
                // $cNs->validate($request, [
                //     "$name" => 'required',
                //     'filenames.*' => 'image|mimes:jpeg,png,jpg,bmp,gif,svg|max:2048',
                // ]);
                $validator = Validator::make($request->all(), [
                    "$name" => 'required',
                    "$name.*" => 'mimes:jpeg,png,jpg,bmp,gif,svg|max:2048',
                    // 'filenames.*' => 'image|mimes:jpeg,png,jpg,bmp,gif,svg|max:2048',
                ], [
                    "$name" . '.required' => "Please upload " . $name . " file.",
                    "$name" . '.mimes' => "Please upload " . $name . " an only a jpeg,png,jpg,bmp,gif,svg file.",
                    "$name" . '.max' => "Please upload " . $name . " a file smaller than 2 MB.",
                ]);
            }

            if ($validator->fails()) {
                // return UtilityController::set_response(400, false, $validator->errors()->first(), []);
                return array('error' => true, 'msg' => $validator->errors()->first());
            }
            // dd($request);
            $path = !empty($path) ? '/images' . $path : '/images';
            if ($request->hasfile("$name")) {
                if (is_array($request->file("$name"))) {
                    foreach ($request->file("$name") as $file) {
                        $name = self::caRand() . '.' . $file->extension();
                        $file->move(public_path() . $path, $name);
                        $data[] = $name;
                    }
                    $data = json_encode($data);
                } else {
                    $file = $request->file("$name");
                    $name =  self::caRand() . '.' . $file->extension();
                    $file->move(public_path() . $path, $name);
                    $data = $name;
                }
            }

            return $data;
        } catch (\Exception $e) {
            Log::error('imageUploadNew');
            Log::error($e->getMessage(), array(json_encode($e)));
            return json_encode($e->getMessage());
        }
    }

    public static function imageUploadNew(Request $request, $name, $path = false, $isPDF = false)
    {
        try {
            // Create an instance of your SupabaseStorageService
            $storageService = new SupabaseService($request);

            $validator = Validator::make($request->all(), [
                "$name" => 'required',
                "$name.*" => $isPDF ? '' : 'mimes:jpeg,png,jpg,bmp,gif,svg',
            ], [
                "$name" . '.required' => "Please upload " . $name . " file.",
                "$name" . '.mimes' => "Please upload " . $name . " a valid file.",
                "$name" . '.max' => "Please upload " . $name . " a file smaller than " . ($isPDF ? "5 MB." : "2 MB."),
            ]);

            if ($validator->fails()) {
                return array('error' => true, 'msg' => $validator->errors()->first());
            }

            $data = [];

            if ($request->hasFile($name)) {
                if (is_array($request->file($name))) {
                    foreach ($request->file($name) as $file) {
                        $fileName = 'ugecy_'. self::caRand() . '.' . $file->extension();
                        // Upload to Supabase
                        $response = $storageService->uploadImage($file->getRealPath(), $fileName);
                        if (isset($response['error'])) {
                            return array('error' => true, 'msg' => $response['error']);
                        }
                        $data[] = $fileName;
                    }
                    $data = json_encode($data);
                } else {
                    $file = $request->file($name);
                    $fileName = self::caRand() . '.' . $file->extension();
                    // Upload to Supabase
                    $response = $storageService->uploadImage($file->getRealPath(), $fileName);
                    if (isset($response['error'])) {
                        return array('error' => true, 'msg' => $response['error']);
                    }
                    $data = $fileName;
                }
            }

            return $data;
        } catch (\Exception $e) {
            Log::error('imageUploadNew');
            Log::error($e->getMessage(), ['exception' => json_encode($e)]);
            return json_encode($e->getMessage());
        }
    }

    public static function caRand()
    {
        return rand(0000000, 9999999) . time() . rand(0000000, 9999999);
    }

    public static function isValidJson($input) {
        json_decode($input);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}