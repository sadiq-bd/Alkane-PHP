<?php

namespace Core;

class Controler extends Load {

    protected $load;

    public function __construct() {
        $this->load = new Load;
    }

}

