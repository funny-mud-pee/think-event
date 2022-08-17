<?php

namespace funnymudpee\thinkphp;

use Closure;

class Event extends \think\Event
{

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
     * @param null $params
     * @param bool $once
     * @param int $mode 默认0<br/>
     * 0 : 将每次监听result与params合并并传递到下一个监听中,合并每次监听的result作为event返回值<br/>
     * 1 : 将每次监听result作为params传递到下一个监听中,将最后一个监听的result作为event返回值<br/>
     * @return array
     */
    public function trigger($event, $params = null, bool $once = false, int $mode = 0)
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
        if (!is_array($params)) {
            $params = [];
        }
        foreach ($listeners as $key => $listener) {
            $result = $this->dispatch($listener, $params);

            if (is_array($result) && !empty($result)) {
                switch ($mode) {
                    case 0:
                    default:
                        $params = array_merge($params, $result);
                        $return = array_merge($return, $result);
                        break;
                    case 1:
                        $params = $result;
                        $return = $result;
                        break;
                }
            }
            if ($once) {
                break;
            }
        }

        return $return;
    }


}