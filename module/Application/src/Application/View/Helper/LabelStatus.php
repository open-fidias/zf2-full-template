<?php

namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Application\Model\OrcamentoStatus;

/**
 * @author atila
 */
class LabelStatus extends AbstractHelper {
    
    public function __invoke($status_id, $status) {
        $template = '<span class="label %s">%s</span>';
        
        switch ($status_id) {
            case OrcamentoStatus::NOVO:
                $cssClass = "label-default";
                break;
            case OrcamentoStatus::ENVIADO:
                $cssClass = "label-success";
                break;
            case OrcamentoStatus::FATURADO:
                $cssClass = "label-warning";
                break;
            case OrcamentoStatus::ABERTO:
                $cssClass = "label-primary";
                break;
            case OrcamentoStatus::CANCELADO:
                $cssClass = "label-danger";
                break;
            default:
                $cssClass = "";
        }
        
        return sprintf($template, $cssClass, $status);
    }
}
