<?php
/**
 * Created by PhpStorm.
 * User: syan
 * Date: 2017/2/2
 * Time: 19:56
 */

namespace App\Helpers;

use SplPriorityQueue;


class Event
{
    private $events = [];

    public function on($event, $callback, $priority = 0)
    {
        if (! isset($this->events[$event])) $this->events[$event] = [];
        $this->events[$event][] = ["fn" => $callback, "prio" => $priority];
    }

    public function has($event)
    {
        return (isset($this->events[$event]) && count($this->events[$event]));
    }

    public function trigger($event, $params = [])
    {
        if ($this->has($event)) {
            $queue = new SplPriorityQueue;
            foreach($this->events[$event] as $index => $action){
                $queue->insert($index, $action["prio"]);
            }
            $queue->top();
            while($queue->valid()){
                $index = $queue->current();
                if (is_callable($this->events[$event][$index]["fn"])){
                    if (call_user_func_array($this->events[$event][$index]["fn"], $params) === false) {
                        break;
                    }
                }
                $queue->next();
            }
        }
    }
}