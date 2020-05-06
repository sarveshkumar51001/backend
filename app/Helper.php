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

function generate_error_slug(string $str)
{
    $error_slug = str_replace('+', '-', urlencode('bkmrk-' . substr(strtolower(preg_replace('/ /', '-', trim($str))), 0, 20)));

    return $error_slug;
}

/**
 * Returns ISO date format from default date format
 *
 * @param $date
 * @return string
 */
function get_iso_date_format($date){

    if(empty($date)) {
       throw new \Exception("Blank Date cannot be converted to ISO format");
    }

    $iso_date = Carbon\Carbon::createFromFormat(\App\Models\ShopifyExcelUpload::DATE_FORMAT,$date)
                                                        ->setTime(date('H'), date('i'), 0)
                                                        ->toIso8601String();
    return $iso_date;
}

function get_job_attempts($job_id) {

    if(Illuminate\Support\Facades\Cache::has($job_id)) {
        $attempts = Illuminate\Support\Facades\Cache::get($job_id);
    } else {
        $attempts = job_attempted($job_id);
    }

    return (string) $attempts;
}

function job_attempted($job_id) {
    $attempts = 0;

    if(Illuminate\Support\Facades\Cache::has($job_id)) {
        $attempts = Illuminate\Support\Facades\Cache::pull($job_id);
    }

    $attempts++;
    Illuminate\Support\Facades\Cache::forever($job_id, $attempts);

    return (string) $attempts;
}

function job_completed($job_id) {
    $attempts = (string) Illuminate\Support\Facades\Cache::pull($job_id);
    return $attempts;
}

function GetStartEndDate($date_range){

    $start_date = start_of_the_day(date('m/d/Y'));
    $end_date = end_of_day(date('m/d/Y'));
    if ($date_range) {
        $range = explode(' - ', $date_range, 2);
        if (count($range) == 2) {
            $start_date = start_of_the_day($range[0]);
            $end_date = end_of_day($range[1]);
        }
    }
    return [$start_date,$end_date];
}

function GetDateRange($date_range){

    $start_date = start_of_the_day(date('d/m/Y'));
    $end_date = end_of_day(date('d/m/Y'));
    if ($date_range) {
        $range = explode(' - ', $date_range, 2);
        if (count($range) == 2) {
            $start_date = \Carbon\Carbon::createFromFormat('d/m/Y',$range[0])->startOfDay()->timestamp;
            $end_date = \Carbon\Carbon::createFromFormat('d/m/Y',$range[1])->endOfDay()->timestamp;
        }
    }
    return [$start_date,$end_date];
}

function GroupByKey($Data,$key)
{
    $groupedData = [];
    foreach ($Data as $data) {
        if(!array_key_exists($key,$data)){
            return [];
        }
        $groupedData[$data[$key]][] = $data;
    }
    return array_values($groupedData);
}

function is_admin() {
	$userPermission = !empty(\Auth::user()->permissions) ? \Auth::user()->permissions : [];
    if(in_array(\App\Library\Permission::PERMISSION_ADMIN, $userPermission)) {
        return true;
    }
    return false;
}

function has_permission($permission) {

    if(is_admin()) {
        return true;
    }

	$userPermission = !empty(\Auth::user()->permissions) ? \Auth::user()->permissions : [];
    if(in_array($permission, $userPermission)) {
        return true;
    }

    return false;
}
function paginate_array($request,$data,$limit)
{
    $currentPage = Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
    $currentPage = 2;
    $collection = collect($data);
    $perPage = $limit;

    $currentPageItems = $collection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();
    $paginatedItems = new Illuminate\Pagination\LengthAwarePaginator($currentPageItems, count($collection), $perPage);
    $paginatedItems->setPath($request->url());

    return $paginatedItems;
}
function string_view_renderer($__php, $__data)
{
    $__data['__env'] = app(\Illuminate\View\Factory::class);
    $obLevel = ob_get_level();
    ob_start();
    extract($__data, EXTR_SKIP);

    try {
        eval('?' . '>' . $__php);
    } catch (Exception $e) {
        while (ob_get_level() > $obLevel) ob_end_clean();
        throw $e;
    } catch (Throwable $e) {
        while (ob_get_level() > $obLevel) ob_end_clean();
        throw new Symfony\Component\Debug\Exception\FatalThrowableError($e);
    }
    return ob_get_clean();
}
