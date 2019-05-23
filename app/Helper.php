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