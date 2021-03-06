<?php

/**
* Plugin de integração com a API do PagSeguro e CakePHP (API oficial do PagSeguro e não montada  a estrutura para a requisição manualmente).
*
* Versão do PHP 5.*
*
*
* @author Andre Cardoso <andrecardosodev@gmail.com>
* @link https://github.com/andrebian/cake-plugin-pagseguro/
* @authorURI http://andrebian.com
* @license MIT (http://opensource.org/licenses/MIT)
* @version 2.1
* @since 2.1
* 
* ESTE PLUGIN UTILIZA A API DO PAGSEGURO, DISPONÍVEL EM  https://pagseguro.uol.com.br/v2/guia-de-integracao/tutorial-da-biblioteca-pagseguro-em-php.html
* 
* 
* 
* PLUGIN BASE (ele é responsável por montar o ambiente para realizar as requisições, o que pode ser incômodo caso a URL do PagSeguro altere por exemplo):
*   https://github.com/ftgoncalves/pagseguro/  de Felipe Theodoro Gonçalves, (http://ftgoncalves.com.br)
*/

$basePath = ROOT . DS;
if (!empty($APP_DIR)) {
    $basePath .= APP_DIR . DS;
}

$vendorDir = $basePath . 'vendor';

$composerDefinitions = json_decode(file_get_contents($basePath . 'composer.json'), true);

if( isset($composerDefinitions['config']) 
    && isset($composerDefinitions['config']['vendor-dir']) 
    && !empty($composerDefinitions['config']['vendor-dir']) ) {
  $vendorDir = $basePath . $composerDefinitions['config']['vendor-dir'];
}

$composerAutoload = $vendorDir . DS . 'autoload.php';

if( !is_file($composerAutoload) ) {
    die(
            'O autoload.php não está presente, isto quer dizer que o composer pode não estar instalado. Visite https://getcomposer.org
             e saiba mais. Caso o composer esteja instalado pode ser que ele não esteja alocando seus arquivos na pasta "vendor(s)"
            '
    );
}

require_once $composerAutoload;

App::import('PagSeguro', 'PagSeguroLibrary', array('file' => $basePath . 'vendor' . DS . 'pagseguro' . DS . 'php' . DS . 'source' . DS . 'PagSeguroLibrary' . DS . 'PagSeguroLibrary.php'));
App::import('Assets', 'PagSeguro.Codes', array('file' => APP . 'Plugin' . DS . 'PagSeguro' . DS . 'Assets' . DS . 'Codes.php'));
App::import('Assets', 'PagSeguro.PagSeguroMoeda', array('file' => APP . 'Plugin' . DS . 'PagSeguro' . DS . 'Assets' . DS . 'PagSeguroMoeda.php'));
App::import('Assets', 'PagSeguro.PagSeguroEntrega', array('file' => APP . 'Plugin' . DS . 'PagSeguro' . DS . 'Assets' . DS . 'PagSeguroEntrega.php'));
App::import('Assets', 'PagSeguro.PagSeguroTiposPagamento', array('file' => APP . 'Plugin' . DS . 'PagSeguro' . DS . 'Assets' . DS . 'PagSeguroTiposPagamento.php'));

class PagSeguroComponent extends Component {
    
    public $credenciais = null;
    public $config;
    
    /**
     * 
     * @param \Controller $controller
     * @throws RuntimeException
     * @since 2.1
     */
    public function startup(\Controller $controller) {
        $this->config = Configure::read('PagSeguro');
        if( empty($this->config) ) {
            throw new RuntimeException('Você precisa definir as configurações básicas do plugin "PagSeguro", leia o manual.');
        }

        if( isset($this->config['isSandbox']) && true === $this->config['isSandbox'] ) {
            PagSeguroConfig::setEnvironment('sandbox');
        }
        
        $this->credenciais = new PagSeguroAccountCredentials($this->config['email'], $this->config['token']);
    }
   
}
