<?php

namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Description of FormatarNumero
 *
 * @author atila
 */
class FormatarNumero extends AbstractHelper {
    
    public function __invoke($valor, $blankWhenNull = false) {
        
        if ($blankWhenNull &&
                (empty($valor) || intval($valor) == 0)
        ) {
            return "";
        }
        
        $fmt = new \NumberFormatter('pt_BR', \NumberFormatter::DECIMAL);
        $fmt->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 2);
        $fmt->setPattern("0.00");
        return $fmt->format($valor);
    }
}
