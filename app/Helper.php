<?php

function get_product_price($productID) {
	$Product = \App\Models\Product::where('product_id', $productID)->first();

	return $Product->product_price ?? 0;
}

function amount_inr_format($amount) {
	$fmt = new \NumberFormatter($locale = 'en_IN', NumberFormatter::DECIMAL);
	return $fmt->format($amount); # Rs 10,00,00,00,000.12
}

/**
 * @param string $date
 * @return int
 */
function start_of_the_day(string $date) {
	return \Carbon\Carbon::createFromTimestamp(strtotime($date . " UTC"))->startOfDay()->timestamp;
}

/**
 * @param string $date
 * @return int
 */
function end_of_day(string $date) {
	return \Carbon\Carbon::createFromTimestamp(strtotime($date. " UTC"))->endOfDay()->timestamp;
}

/**
 * Return instance of library for posting notifications on Slack
 * @param mixed $data
 * @param string $title
 * @return \App\Library\Slack\Slack
 */
function slack($data = array(), string $title = null)
{
    return new \App\Library\Slack\Slack($data, $title);
}

function isArrayAssoc(array $arr)
{
    if (array() === $arr) return false;
    return array_keys($arr) !== range(0, count($arr) - 1);
}


function array_contains_empty_value(array $arr) {
    return boolval(count(array_filter($arr)) !== count($arr));
}

function log_error(\Exception $e) {
    Illuminate\Support\Facades\Log::error($e);
    slack($e)->post();
}

/**
 * Returns Webhook Event Class path
 *
 * @param App\Models\Webhook $Webhook
 * @return string|boolean
 */
function webhook_event_class(App\Models\Webhook $Webhook) {

    $namespace = '\App\Library\Webhook\Events';
    $source = \Illuminate\Support\Str::title($Webhook->{App\Models\Webhook::SOURCE});
    $class_name = \Illuminate\Support\Str::studly($Webhook->{App\Models\Webhook::NAME});
    $class_path = sprintf("%s\%s\%s", $namespace, $source, $class_name);

    if (class_exists($class_path)) {
        if (method_exists($class_path, 'handle')) {
            return $class_path;
        }
    }
    return false;
}

function is_date_later_than_months($date, $months){

    $diff_in_months = \Carbon\Carbon::now()->diffInMonths(\Carbon\Carbon::createFromFormat(\App\Models\ShopifyExcelUpload::DATE_FORMAT, $date), false);

    if($diff_in_months > $months ){
        return true;
    }
    return false;
}
