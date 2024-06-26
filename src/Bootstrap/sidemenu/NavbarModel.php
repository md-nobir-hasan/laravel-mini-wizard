<?php

namespace Nobir\CurdByCommand\Module;

class Navbar
{
    public $title;
    public $access;
    public $route;
    public $n_sidebar_id;
    public $serial;


    public function toArray()
    {
        return [
            'title' => $this->title,
            'access' => $this->access,
            'n_sidebar_id' => $this->n_sidebar_id,
            'route' => $this->route,
            'serial' => $this->serial(),
        ];
    }

    public function serial()
    {
        return $this->serial;
    }
}

?>
<!--  -->
