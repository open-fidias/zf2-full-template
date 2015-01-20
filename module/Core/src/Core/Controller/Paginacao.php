<?php

namespace Core\Controller;

class Paginacao {
    
    public function getOffset($page) {
        $page = (int) $page;
        
        if ($page < 0) {
            return 0;
        }
        
        $page = $page - 1;
        return $page * 10;
    }
}