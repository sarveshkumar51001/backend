<?php

namespace App\Http\Controllers;

use App\Models\WebhookNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class NotificationController extends BaseController
{
    public static $validation_rules =  [
        "source" => "required|string",
        "event" => "required|string",
        "type" => "required|string",
        "subject" => "required",
        "page_id" => "required|alpha_num",
        "to_name" => "required|string",
        "to_email" =>"required|string",
        "cutoff_date" => "required",
        "file" => "max:3072"
    ];

    public static function getDocuments(){
        return WebhookNotification::all()->toArray();
    }
    public function index()
    {
        $breadcrumb = ['Notifications' => ''];
        return view('vendor.notifications.notifications-list', ['breadcrumb' => $breadcrumb,'documents'=> self::getDocuments()]);
    }

    public function get($id)
    {
        $breadcrumb = ['Notifications' => ''];

        $document = WebhookNotification::find($id)->toArray();

        return view('vendor.notifications.notifications-list',['breadcrumb' => $breadcrumb,'documents'=> self::getDocuments(),'data'=>$document]);
    }

    public function create(Request $request)
    {
        $breadcrumb = ['Notifications' => ''];

        $data = $request->all();

        $validator = Validator::make($data, self::$validation_rules);
        $errors = Arr::flatten(array_values($validator->getMessageBag()->toArray()));

        $file = $request->file('file');
        $originalFileName = $file->getClientOriginalName();
        $filePath = storage_path(sprintf('uploads/%s',$data['page_id']));
        $path = $file->move($filePath, $originalFileName);

        $notification_doc = [
            "identifier" => $data['event'],
            "source" => $data['source'],
            "data" => [
                "page_id" => $data['page_id'],
                "subject" => $data['subject'],
                "to_name" => $data['to_name'],
                "to_email" => $data['to_email'],
                "template" => $data['email_template'],
                "attachments" => [$path->getRealPath()],
                "cutoff_datetime" => $data['cutoff_date'],
                "test_mode" => isset($data['test']) ? 1 : 0,
                "active" => isset($data['active'] ) ? 1 : 0,
            ],
            "channel" => $data['type']
        ];

        $notification = WebhookNotification::create($notification_doc);
        $data = $notification->exists() ? 'Yes': '';

        return view('vendor.notifications.notifications-list',['errors'=>$errors,'breadcrumb'=>$breadcrumb,'documents'=>  self::getDocuments(),'notification' => $data]);
    }

    public function update(Request $request , $id)
    {

        $notification = WebhookNotification::find($id);

        if(!$notification instanceof WebhookNotification) {
            return response([
                'message' => 'Record not found'
            ], 404);
        }

        return false;
    }






}
