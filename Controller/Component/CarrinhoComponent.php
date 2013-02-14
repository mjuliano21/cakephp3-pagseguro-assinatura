<?php
/**
* Plugin de integração com a API do PagSeguro e CakePHP (API de verdade, não somente montar a string e enviar por post).
*
* Versão do PHP 5.*
*
*
* @author Andre Cardoso <andrecardosodev@gmail.com>
* @link https://github.com/andrebian/cake-plugin-pagseguro/
* @authorURI http://andrebian.com
* @license MIT License (http://www.opensource.org/licenses/mit-license.php)
* @version 1.0
* 
* ESTE PLUGIN UTILIZA A API DO PAGSEGURO, DISPONÍVEL EM  https://pagseguro.uol.com.br/v2/guia-de-integracao/tutorial-da-biblioteca-pagseguro-em-php.html
* 
* 
* 
* PLUGIN BASE (enviando dados via POST):
*   https://github.com/ftgoncalves/pagseguro/  de Felipe Theodoro Gonçalves, (http://ftgoncalves.com.br)
*/

App::uses('HttpSocket', 'Network/Http');
App::uses('PagSeguroLibrary', 'Plugin/PagSeguro/Vendor/PagSeguroLibrary');

class CarrinhoComponent extends Component{
    
    private $loadPagSeguroLibrary = null;
    private $credenciais = null;
    private $montaPagamento = null;
    private $comprador = null;
    private $Controller = null;
    
   
/**
 * 
 * @param \Controller $Controller
 */    
    public function startup(\Controller $Controller) {
        
        // Instanciando classes para gerar o pagamento
        $this->loadPagSeguroLibrary = new PagSeguroLibrary;
        $this->montaPagamento = new PagSeguroPaymentRequest();
        $this->comprador = new PagSeguroSender;
        
        
        // definindo alguns dados padrões
        $this->montaPagamento->setShippingType('3');
        $this->montaPagamento->setCurrency('BRL');
               
        parent::startup($Controller);
    }
  
    
 /**
  * Define as credenciais para utilização do PagSeguro
  * 
  * @param string $email
  * @param string $token
  */   
    public function setCredenciais($email, $token) {
        $this->credenciais = new PagSeguroAccountCredentials($email, $token);
    }
    
    
 /**
  * Define uma URL para após a finalização da interação do usuário com o PagSeguro
  * 
  * @param string $urlRetorno
  */   
    public function setUrlRetorno($urlRetorno = null) {
       $this->montaPagamento->setRedirectURL($urlRetorno); 
    }
    
    
 /**
  * Seta a referência para a compra
  * 
  * @param int $id
  */
    public function setReferencia($id) {
        $this->montaPagamento->setReference($id);
    }
    
    
/**
 * Adiciona um único item de cada vez ao "carrinho de compras"
 * 
 * @param int $id
 * @param string $nomeProduto
 * @param float $valorUnit
 * @param int $peso
 * @param int $quantidade
 * @param float $frete
 */
    public function adicionarItem($id, $nomeProduto, $valorUnit, $peso, $quantidade = 1, $frete = null) {
        $this->montaPagamento->addItem($id, $nomeProduto, $quantidade, $valorUnit, $peso, $frete);
    }
    
    
 /**
  * Define os dados de contato do comprador
  * 
  * @param string $nome
  * @param string $email
  * @param string $codigoArea
  * @param string $numeroTelefone
  */   
    public function setContatosComprador($nome, $email, $codigoArea, $numeroTelefone) {
        $this->montaPagamento->setSenderName($nome);
        $this->montaPagamento->setSenderEmail($email);
        $this->montaPagamento->setSenderPhone($codigoArea, $numeroTelefone);
    }
    
    
 /**
  * Define o endereço do comprador
  * 
  * @param string $cep
  * @param string $rua
  * @param string $numero
  * @param string $complemento
  * @param string $bairro
  * @param string $cidade
  * @param string $uf
  * @param string $pais
  */   
    public function setEnderecoComprador($cep, $rua, $numero, $complemento, $bairro, $cidade, $uf, $pais = 'BRA') {
        $this->montaPagamento->setShippingAddress($cep, $rua, $numero, $complemento, $bairro, $cidade, $uf, $pais);
    }
    
    
 /**
  * E enfim enviando o usuário para o pagamento
  * 
  * @return boolean
  */   
    public function finalizaCompra() {
        if ($url = $this->montaPagamento->register($this->credenciais) ) {
            return $url;
        }
    }
    
}