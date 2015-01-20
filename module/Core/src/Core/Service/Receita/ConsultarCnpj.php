<?php

/**
 * 
 */

namespace Core\Service\Receita;

use Zend\Http\Client;
use Zend\Dom\Query;
use Zend\Dom\NodeList;

// caminho para salvar cookies como arquivos
define('COOKIELOCAL', 'public/tmp/');
// índice do campo "ATIVIDADE SECUNDARIA"
define('IDX_ATIVIDADE_SECUNDARIA', 7);

class ConsultarCnpj {

    const BASE_URL = 'http://www.receita.fazenda.gov.br';
    const URL_SOLICITACAO = 'http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/Cnpjreva_Solicitacao2.asp';
    const URL_VALIDAR = 'http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/valida.asp';
    const FAKE_USERAGENT = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:8.0) Gecko/20100101 Firefox/8.0';
    const CSS_SELECTOR = 'font[style~="8pt"] > b';
    
    /**
     * Após fazer parse do arquivo HTML os dados referentes ao CNPJ estão
     * com os índices indicados abaixo
     * @var array
     */
    private $_dadosCnpjTemplate = array(
        0 => 'cpf_cnpj',
        3 => 'nome',
        4 => 'nome_fantasia',
        //15 => 'situacao',
    );
    
    /**
     * Após fazer parse do arquivo HTML os dados referentes ao Endereço estão
     * com os índices indicados abaixo
     * @var array
     */
    private $_enderecoTemplate = array(
        8 => 'endereco',
        9 => 'numero',
        10 => 'complemento',
        11 => 'cep',
        12 => 'bairro',
        13 => 'municipio',
        14 => 'estado'
    );

    protected $_httpClient;

    public function __construct() {
        $this->_httpClient = new Client();
    }

    public function obterCaptcha() {
        $html = $this->requisitarCaptcha();
        if ($html) {
            $dom = new Query($html);
            $captcha = new \stdClass;

            $captcha->url_img = ConsultarCnpj::BASE_URL . $this->getAttributeById($dom, '#imgcaptcha', 'src');

            $ch = curl_init($captcha->url_img);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $imgsource = curl_exec($ch);
            curl_close($ch);
            
            $captcha->img_base64 = base64_encode($imgsource);

            $captcha->viewstate = $this->getAttributeById($dom, '#viewstate', 'value');
            return $captcha;
        } else {
            return false;
        }
    }
    
    /**
     * Faz requisição GET na página inicial onde tem o CAPTCHA e o viewstate,
     * parâmetros necessários para obtenção dos dados do cliente.
     * @return boolean
     */
    private function requisitarCaptcha() {
        // gerar arquivo para salvar cookie para ser usado para trazer os dados do CNPJ
        $cookieFile = COOKIELOCAL . session_id();
        if (!file_exists($cookieFile)) {
            $file = fopen($cookieFile, 'w');
            fclose($file);
        }

        $ch = curl_init(ConsultarCnpj::URL_SOLICITACAO);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        $html = curl_exec($ch);
        if ($html) {
            return $html;
        }
        return false;
    }

    public function obterDadosCnpj($cnpj, $captcha, $viewstate) {
        $html = $this->getHtmlCNPJ($cnpj, $captcha, $viewstate);
        if (! $html) {
            throw new \Exception('Serviço de consulta de CNPJ está fora do ar, tente mais tarde.');
        }
        $dom = new Query($html);
        $pf = new \stdClass;
        
        $results = $dom->execute(ConsultarCnpj::CSS_SELECTOR);
        if(count($results) == 0) {
            throw new \Exception('CNPJ não encontrado. Verifique se o CAPTCHA e o CNPJ estão corretos e tente novamente.');
        }
        
        $dadosCnpf = array();
        foreach ($this->_dadosCnpjTemplate as $key => $value) {
            $dadosCnpf[$value] = trim($results->offsetGet($key)->nodeValue);
        }
        
        $skip_idx = $this->skipAtividadeSecundaria($results);
        $dadosEndereco = $this->parseEndereco($results, $skip_idx);
        
        $idx_situcao = 15;
        $situacao = trim($results->offsetGet($idx_situcao + $skip_idx)->nodeValue);
        $dadosCnpf['situacao'] = ($situacao == 'ATIVO' ? true : false);
        
        $pf->cnpj = $dadosCnpf;
        $pf->endereco = $dadosEndereco;
        return $pf;
    }
    
    /**
     * 
     * pegar o endereço corretamente.
     * @param \Zend\Dom\NodeList $list
     * @return type
     */
    private function parseEndereco(NodeList $list, $skip_idx) {
        $dadosEndereco = array();
        foreach ($this->_enderecoTemplate as $key => $value) {
            $dadosEndereco[$value] = trim($list->offsetGet($key + $skip_idx)->nodeValue);
        }
        return $dadosEndereco;
    }

    /**
     * Devido ao campo "CÓDIGO E DESCRIÇÃO DAS ATIVIDADES ECONÔMICAS SECUNDÁRIAS"
     * poder possuir mais de um valor, é preciso pular (skip) esses valores para
     * pegar o endereço corretamente.
     * @param \Zend\Dom\NodeList $list
     * @return type
     */
    private function skipAtividadeSecundaria(NodeList $list) {
        $len = count($list);
        $skip_idx = 0;
        for ($i = IDX_ATIVIDADE_SECUNDARIA; $i < $len; $i++) {
            $node = trim($list->offsetGet($i)->nodeValue);
            if (preg_match('/^\d{2}\.\d{2}-\d-\d{2}/', $node)) {
                $skip_idx++;
                continue;
            }
            break;
        }
        return $skip_idx;
    }

    /**
     * Faz uma requisição POST para obter a página de retorno contendo os dados do
     * CNPJ do cliente.
     * @param type $cnpj
     * @param type $captcha
     * @param type $viewstate
     * @return boolean
     */
    private function getHtmlCNPJ($cnpj, $captcha, $viewstate) {
        // carrega o arquivo contendo o cookie da sessão anterior
        $cookieFile = COOKIELOCAL . session_id();
        if (!file_exists($cookieFile)) {
            return false;
        }

        // parâmentros necessários para requisição post
        $params = array
            (
            'origem' => 'comprovante',
            'search_type' => 'cnpj',
            'cnpj' => $cnpj,
            'captcha' => $captcha,
            'captchaAudio' => '',
            'submit1' => 'Consultar',
            'viewstate' => $viewstate
        );

        $post = http_build_query($params, NULL, '&');

        // cookie adicional
        $cookie = array('flag' => 1);

        $ch = curl_init(ConsultarCnpj::URL_VALIDAR);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_USERAGENT, ConsultarCnpj::FAKE_USERAGENT);
        curl_setopt($ch, CURLOPT_COOKIE, http_build_query($cookie, NULL, '&'));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_REFERER, ConsultarCnpj::URL_SOLICITACAO);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $html = curl_exec($ch);
        curl_close($ch);
        
        unlink($cookieFile); // apagar arquivo de cookie
        
        return $html;
    }

    /*public function obterDadosCnpj($cnpj, $captcha, $viewstate) {
        if (isset($_SESSION['cookiejar']) &&
                $_SESSION['cookiejar'] instanceof Cookies) {
            $cookieJar = $_SESSION['cookiejar'];
        } else {
            throw new \Exception("Não foi possível fazer a conexão, sessão expirada.");
        }
        
        $cookies = $cookieJar->getMatchingCookies(ConsultarCnpj::BASE_URL);

        $params = array(
            'origem' => 'comprovante',
            'search_type' => 'cnpj',
            'cnpj' => $cnpj,
            'captcha' => $captcha,
            'captchaAudio' => '',
            'submit1' => 'Consultar',
            'viewstate' => $viewstate
        );
        $this->_httpClient->setUri(ConsultarCnpj::URL_VALIDAR);
        $this->_httpClient->setMethod('post');
        
        //$cookies = array(
        //    'flag' => 1
        //);
        //$cookieJar = array_merge($cookies, $cookieJar->getMatchingCookies(ConsultarCnpj::URL_SOLICITACAO));
        $this->_httpClient->setCookies($cookies);
        
        $this->_httpClient->setParameterPost($params);
        $response = $this->_httpClient->send();
        if ($response->isSuccess()) {
            return $response->getContent();
        } else {
            return null;
        }
    }*/

    private function getAttributeById($dom, $id, $attribute) {
        $results = $dom->execute($id);
        $node = $results->current();
        $src = $node->attributes->getNamedItem($attribute);
        return $src->nodeValue;
    }
}
