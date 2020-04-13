<?php

namespace App\Http\Controllers;

use App\Models\WebhookNotification;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class NotificationController extends BaseController
{

    const SOURCES = [
        'instapage'
    ];

    const EVENTS = [
        'lead_create'
    ];

    const CHANNELS = [
        'email',
//        'sms'
    ];

    public static $validation_rules = [
        "source" => "required|string",
        "event" => "required|string",
        "type" => "required|string",
        "subject" => "required|string",
        "page_id" => "required|alpha_num",
        "to_name" => "required|string",
        "to_email" => "required|string",
        "cutoff_date" => "required",
        "file" => "max:3072"
    ];

    /**
     * @return ResponseFactory|Factory|Response|View
     */
    public function index()
    {
        if (!is_admin()) {
            return \response('You don\'t have the access to view this page.Please check with the administrator.', 403);
        }

        $data = WebhookNotification::paginate(20);

        $breadcrumb = ['Notifications' => ''];
        return view('notifications.index', ['breadcrumb' => $breadcrumb, 'data' => $data])->with($this->getDefaultData());
    }

    public function create()
    {
        return view('notifications.create-edit')->with($this->getDefaultData());
    }

    /**
     * Function for returning single notification on request...
     *
     * @param $id
     * @return Factory|View
     */
    public function edit($id)
    {
        $breadcrumb = ['Notifications' => ''];

        if (!is_admin()) {
            return \response('You don\'t have the access to view this page.Please check with the administrator.', 403);
        }

        $document = WebhookNotification::find($id);

        if (!$document) {
            return response('Notification to be edited not found in the database', 403);
        }

        return view('notifications.create-edit', ['breadcrumb' => $breadcrumb, 'data' => $document->toArray()])->with($this->getDefaultData());

    }

    /**
     * Function for creating/updating the notification as per the request...
     *
     * @param Request $request
     * @return Factory|View
     */
    public function store(Request $request)
    {
        $breadcrumb = ['Notifications' => ''];

        if (!is_admin()) {
            return \response('You don\'t have the access to view this page.Please check with the administrator.', 403);
        }

        $data = $request->all();
        $status = $real_path = '';

        $validator = Validator::make($data, self::$validation_rules);
        $errors = Arr::flatten(array_values($validator->getMessageBag()->toArray()));

        // Proceeding further iff no errors found....
        if (empty($errors)) {

            $file = $request->file('file');

            if (!empty($file)) {
                $originalFileName = $file->getClientOriginalName();
                $filePath = storage_path(sprintf('uploads/%s', $data['page_id']));
                $path = $file->move($filePath, $originalFileName);
                $real_path = $path->getRealPath();
            }

            $notification_doc = [
                "identifier" => $data['event'],
                "source" => $data['source'],
                "data" => [
                    "page_id" => $data['page_id'],
                    "subject" => $data['subject'],
                    "to_name" => $data['to_name'],
                    "to_email" => $data['to_email'],
                    "template" => $data['email_template'],
                    "attachments" => !empty($real_path) ? [$real_path] : '',
                    "cutoff_datetime" => strtotime($data['cutoff_date']),
                    "test_mode" => isset($data['test']) ? 1 : 0,
                    "active" => isset($data['active']) ? 1 : 0,
                ],
                "channel" => $data['type']
            ];
            // If the request has update param then update the notification else create a new one...
            WebhookNotification::create($notification_doc);
            }

        return view('notifications.create-edit', ['errors' => $errors, 'breadcrumb' => $breadcrumb, 'notification' => 'create'])->with($this->getDefaultData());
    }

    public function update(Request $request,$id)
    {
        
        if (!is_admin()) {
            return \response('You don\'t have the access to view this page.Please check with the administrator.', 403);
        }

        $data = $request->all();
        $status = $real_path = '';

        $validator = Validator::make($data, self::$validation_rules);
        $errors = Arr::flatten(array_values($validator->getMessageBag()->toArray()));


        if (empty($errors)) {

            $file = $request->file('file');

            if (!empty($file)) {
                $originalFileName = $file->getClientOriginalName();
                $filePath = storage_path(sprintf('uploads/%s', $data['page_id']));
                $path = $file->move($filePath, $originalFileName);
                $real_path = $path->getRealPath();
            }

            $notification = WebhookNotification::where('data.page_id', $data['page_id']);

            if (!$notification->exists()) {
                return response([
                    'Record not found'
                ], 403);
            }

            $notification->update(['data.page_id' => $data['page_id'],
                'data.subject' => $data['subject'],
                'data.to_name' => $data['to_name'],
                'data.to_email' => $data['to_email'],
                'data.template' => $data['email_template'],
                'data.cutoff_datetime' => $data['cutoff_date'],
                'data.test_mode' => isset($data['test']) ? 1 : 0,
                'data.active' => isset($data['active']) ? 1 : 0
            ]);

            if (empty($notification->first()->data['attachments'])) {
                $notification->update(['data.attachments' => [$real_path]]);
            }
        }

        return view('notifications.create-edit', ['errors' => $errors, 'breadcrumb' => ['Notifications' => ''], 'notification' => 'update'])->with($this->getDefaultData());
    }

    private function getDefaultData() {
        return [
            'sources' => self::SOURCES,
            'events' => self::EVENTS,
            'channels' => self::CHANNELS,
        ];
    }
}
