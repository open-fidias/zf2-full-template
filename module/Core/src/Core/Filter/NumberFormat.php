<?php

namespace Core\Filter;

use Zend\Filter\AbstractFilter;

/**
 * Description of NumberFormat
 *
 * @author
 */
class NumberFormat extends AbstractFilter {
    
    public function filter($valor) {
        
        if (empty($valor)) {
            return 0;
        }
        // O uso de !== false não é atoa; ver: http://stackoverflow.com/questions/4366730/how-to-check-if-a-string-contains-specific-words
        else if (strpos($valor, '.') !== false) {
            return number_format($valor, 2, ',', '');
        } else {
            $valor = (float) str_replace(",", ".", $valor);
            return $valor;
        }
    }
}
