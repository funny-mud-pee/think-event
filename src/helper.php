<?php

use think\facade\Event;

/**
 * 触发事件
 * @param mixed $event 事件名(或者类名)
 * @param mixed $args 参数
 * @param bool $once 仅仅返回事件中第一个有效监听的结果
 * @param int $mode 默认0<br/>
 * 0 : 将每次监听result与params合并并传递到下一个监听中,合并每次监听的result作为event返回值<br/>
 * 1 : 将每次监听result作为params传递到下一个监听中,将最后一个监听的result作为event返回值<br/>
 * @return array
 */
function event_trigger($event, $args = null, bool $once = false, int $mode = 0): array
{
    return Event::trigger($event, $args, $once, $mode);
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