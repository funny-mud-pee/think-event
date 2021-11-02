<?php

use think\facade\Event;

/**
 * 触发事件
 * @param mixed $event 事件名（或者类名）
 * @param mixed $args 参数
 * @return array
 */
function event_trigger($event, $args = null): array
{
    return Event::trigger($event, $args);
}

/**
 * 监听事件
 * @param $event
 * @param $listener
 */
function event_listen($event, $listener = null)
{
    if (is_array($event)) {
        Event::listenEvents($event);
    } elseif (is_array($listener)) {
        Event::listenEvents([$event => $listener]);
    } else {
        Event::listen($event, $listener);
    }
}

/**
 * 取消事件
 * @param $event
 * @param $listener
 */
function event_cancel($event, $listener = null)
{
    Event::cancel($event, $listener);
}