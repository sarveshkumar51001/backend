<?php
namespace App\Http\Middleware\Webhooks;

use Closure;
use App\Models\Webhook;

class Shopify
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $hmac_header = $request->header('x-shopify-hmac-sha256', null);

        $calculated_hmac = base64_encode(hash_hmac('sha256', $request->getContent(), env('SHOPIFY_WEBHOOK_SECRET', null), true));
        if ($hmac_header == $calculated_hmac) {
            
            $webhook_id = $request->webhook_id;
            $Webhook = Webhook::find($webhook_id);
            $Webhook->{Webhook::ISAUTHENTICATED} = true;
            $Webhook->save();
            
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Shopify Webhook Token Mismatch'
        ], 406);
    }
}
