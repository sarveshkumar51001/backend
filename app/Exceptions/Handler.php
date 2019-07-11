<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
	/**
	 * A list of the exception types that are not reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		AuthenticationException::class,
		NotFoundHttpException::class
	];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

	/**
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  \Exception $exception
	 *
	 * @return void
	 * @throws Exception
	 */
	public function report(Exception $exception)
	{
		parent::report($exception);

		if ($this->shouldReport($exception)) {
			// Run your custom code here
			
			// Post Exception on Slack
		    slack($exception)->post();
		}

    	parent::report($exception);
	}
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        return parent::render($request, $exception);
    }

	/**
	 * Post message on slack
	 *
	 * @param string $payload
	 */
	private static function PostOnSlack(string $payload) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,env('SLACK_WEBHOOK', null));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt( $ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_exec($ch);
		curl_close($ch);
	}

	/**
	 * @param Exception $exception
	 *
	 * @return string
	 */
	private static function GetPayload(Exception $exception) {
		$attachments = [];

		$attachments['title'] = 'An exception occurred on Backend server';
		$attachments['color'] = '#FF0000';

		$message = "EXCEPTION " . $exception->getMessage() . "\n";
		$message .= "originates: " . $exception->getFile() . " (" . $exception->getLine() . ")\n";
		$attachments['text'] = $message;

		$data['time'] = date("c", time());
		$data['env'] = env('APP_URL');
		$data['url'] = request()->fullUrl();
		$data['exception_class'] = get_class($exception);

		$fields = [];
		foreach ($data as $key => $value) {
			$fields[] = [
				'title' => $key,
				'value' => $value,
				'short' => true
			];
		}

		$attachments['fields'] = $fields;

		$payload['attachments'][] = $attachments;

		return json_encode($payload);
	}
}
