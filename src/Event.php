<?php

namespace funnymudpee\thinkphp;

use Closure;

class Event extends \think\Event
{

    /**
     * 注册事件监听
     * @access public
     * @param string $event 事件名称
     * @param mixed $listener 监听操作（或者类名）
     * @param bool $first 是否优先执行
     * @return $this
     */
    public function listen(string $event, $listener, bool $first = false)
    {
        if (isset($this->bind[$event])) {
            $event = $this->bind[$event];
        }
        $key = $this->key($listener);
        if (is_null($key)) {
            return parent::listen($event, $listener, $first);
        }
        if ($first && isset($this->listener[$event])) {
            unset($this->listener[$event][$key]);
            $this->listener[$event] = [$key => $listener] + $this->listener[$event];
        } else {
            $this->listener[$event][$key] = $listener;
        }

        return $this;
    }

    private function key($listener)
    {
        if ($listener instanceof Closure) {
            return null;
        }
        if (is_callable($listener) && is_array($listener)) {
            $class = $listener[0];
            if (is_object($class)) {
                $class = get_class($class);
            }
            $tag = $class . '::' . $listener[1];
        } else {
            $tag = $listener;
        }
        return md5($tag);
    }

    /**
     * 批量注册事件监听
     * @access public
     * @param array $events 事件定义
     * @return $this
     */
    public function listenEvents(array $events)
    {
        foreach ($events as $event => $listeners) {
            if (isset($this->bind[$event])) {
                $event = $this->bind[$event];
            }
            foreach ($listeners as $listener) {
                $this->listen($event, $listener);
            }
        }
        return $this;
    }

    /**
     * @param object|string $event
     * @param mixed $params
     * @param bool $once
     * @return array|mixed
     */
    public function trigger($event, $params = null, bool $once = false)
    {
        if (is_object($event)) {
            $params = $event;
            $event = get_class($event);
        }
        if (isset($this->bind[$event])) {
            $event = $this->bind[$event];
        }
        $listeners = $this->listener[$event] ?? [];
        $listeners = array_unique($listeners, SORT_REGULAR);
        $return = [];
        foreach ($listeners as $key => $listener) {
            $result = $this->dispatch($listener, $params);
            if (!is_array($result)) {
                $result = [];
            }
            $params = array_merge($params, $result);
            $return = array_merge_recursive($return, $result);
        }
        return $return;
    }

    public function listeners()
    {
        return $this->listener;
    }


    /**
     * 移除事件监听
     * @access public
     * @param string $event 事件名称
     * @param $listener
     * @return void
     */
    public function cancel(string $event, $listener): void
    {
        if (isset($this->bind[$event])) {
            $event = $this->bind[$event];
        }
        if (is_null($listener)) {
            $this->listener[$event] = [];
        } else {
            $key = $this->key($listener);
            if (!is_null($key)) {
                unset($this->listener[$event][$key]);
            }
        }
    }
}