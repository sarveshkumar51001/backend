<?php

/**
 * Return instance of library for posting notifications on Slack
 * @param mixed $data
 * @param string $title
 * @return \App\Library\Slack\Slack
 */
function slack($data, string $title = null)
{
    return new \App\Library\Slack\Slack($data, $title);
}