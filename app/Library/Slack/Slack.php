<?php
namespace App\Library\Slack;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use function Sentry\configureScope;
use Sentry\State\Scope;
use GuzzleHttp;

class Slack
{

    private $color = self::COLOR_INFO;

    private $payload;

    private $response;

    private $webhook_url = null;

    private $data = [];

    private $attachment = [];

    const COLOR_ERROR = '#FF0000';

    const COLOR_WARN = '#FFAE42';

    const COLOR_SUCCESS = '#28A745';

    const COLOR_INFO = '#007BFF';

    public function __construct($data = null, string $title = null)
    {
        if (! empty($data)) {
            if ($data instanceof \Exception) {
                $this->exception($data);
            } elseif (isArrayAssoc($data)) {
                if (empty($title)) {
                    throw new \Exception('Title is required with custom message');
                }
                $this->message($data, $title);
            } else {
                throw new \Exception('Should be either an Instance of an Associative Array or Exception');
            }
        }
    }

    /**
     * Set Exception
     *
     * @param \Exception $exception
     * @return \App\Library\Slack\Slack
     */
    public function exception(\Exception $exception)
    {
        $this->error();

        $attachment = $data = [];

        if (app()->bound('sentry')) {
            $module = str_replace("Controller", "", pathinfo($exception->getFile(), PATHINFO_FILENAME));
            configureScope(function (Scope $scope) use ($module): void {
                $scope->setUser([
                    'Name' => Auth::user()->name ?? 'NA'
                ]);
                $scope->setTag('Module', $module);
                $scope->setExtra('Session Data', session()->all());
            });
            $data['SENTRY EVENT'] = app('sentry')->captureException($exception);
        }

        $message = sprintf("EXCEPTION %s \noriginates: %s (%s)\n", $exception->getMessage(), $exception->getFile(), $exception->getLine());

        $attachment['text'] = $message;
        $attachment['title'] = 'An exception occurred on Backend ' . env('APP_ENV') . ' Server';

        $data['EXCEPTION_CLASS'] = get_class($exception);
        $data['EXCEPTION_MESSAGE'] = $exception->getMessage();
        $data['USER'] = Auth::user()->name ?? 'NA';
        $data['TIME'] = date("d-M-Y g:i:s a", time());
        $data['ENV'] = env('APP_URL');
        $data['URL'] = request()->fullUrl();

        $this->data = $data;
        $this->attachment = $attachment;

        return $this;
    }

    public function message(array $data, string $title)
    {
        $this->attachment['title'] = $title;
        $this->data = $data;
        return $this;
    }

    public function webhook($webhook_url)
    {
        $this->webhook_url = $webhook_url;

        return $this;
    }

    public function post()
    {
        if (app()->isLocal()) {
            logger($this->getPayload());
        }
        
        if (! empty($this->getSlackWebhook())) {

            $request = new Client();
            $response = $request->post($this->getSlackWebhook(), [
                GuzzleHttp\RequestOptions::BODY => $this->getPayload()
            ]);

            $this->response = $response;

            return $this;
        }

        return false;
    }

    public function response()
    {
        if (empty($this->response)) {
            throw new \Exception('No Response Found');
        }

        return $this->response;
    }

    /**
     * Set Attachment color to blue (#007BFF)
     *
     * @return \App\Library\Slack\Slack
     */
    public function info()
    {
        $this->color = self::COLOR_INFO;

        return $this;
    }

    /**
     * Set Attachment color to orange (#FFAE42)
     *
     * @return \App\Library\Slack\Slack
     */
    public function warn()
    {
        $this->color = self::COLOR_WARN;

        return $this;
    }

    /**
     * Set Attachment color to red (#FF0000)
     *
     * @return \App\Library\Slack\Slack
     */
    public function error()
    {
        $this->color = self::COLOR_ERROR;

        return $this;
    }

    /**
     * Set Attachment color to red (#28A745)
     *
     * @return \App\Library\Slack\Slack
     */
    public function success()
    {
        $this->color = self::COLOR_SUCCESS;

        return $this;
    }

    private function setPayload(array $data = [], array $attachment = [])
    {
        if (empty($data) && empty($attachment)) {
            throw new \Exception("No data to post on Slack");
        }

        $payload = [];

        $attachment['color'] = $this->color;

        $fields = [];
        foreach ($data as $key => $value) {
            if ($key == 'SENTRY EVENT') {
                $value = sprintf("<https://sentry.io/valedra/backend/?query=%s|%s>", $value, $value);
            }

            $fields[] = [
                'title' => $key,
                'value' => $value,
                'short' => true
            ];
        }

        $attachment['fields'] = $fields;

        $payload['attachments'][] = $attachment;

        $this->payload = json_encode($payload);

        return $this;
    }

    private function getSlackWebhook()
    {
        $slack_webhook_url = $this->webhook_url ?? env('SLACK_WEBHOOK', null);

        return $slack_webhook_url;
    }

    private function getPayload()
    {
        $this->setPayload($this->data, $this->attachment);
        return $this->payload;
    }
}