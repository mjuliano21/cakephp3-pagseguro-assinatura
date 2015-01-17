<?php
/**
* Plugin de integração com a API de notificações do PagSeguro e CakePHP.
*
* Versão do PHP 5.*
*
*
* @author Andre Cardoso <andrecardosodev@gmail.com>
* @link https://github.com/andrebian/cake-plugin-pagseguro/
* @authorURI http://andrebian.com
* @license MIT (http://opensource.org/licenses/MIT)
* @version 2.1
* @since 1.1
* 
* ESTE PLUGIN UTILIZA A API DO PAGSEGURO, DISPONÍVEL EM  (https://pagseguro.uol.com.br/v2/guia-de-integracao/tutorial-da-biblioteca-pagseguro-em-php.html)
* 
*/

class NotificacaoComponent extends PagSeguroComponent {
    
    protected $dadosTransacao = null;
    
    
/**
  * Define as credenciais para utilização do PagSeguro
  * 
  * @deprecated since version 2.1
  * Use NotificacaoComponent::defineCredenciais para novas chamadas
  *  
  * @param string $email
  * @param string $token
  * @since 1.0
  */   
    public function setCredenciais($email, $token) {
        $this->defineCredenciais($email, $token);
    }
    
    /**
     * Define as credenciais para utilização do PagSeguro
     * 
     * @param string $email
     * @param string $token
     * @since 2.1
     */
    public function defineCredenciais($email, $token) {
        $this->credenciais = new PagSeguroAccountCredentials($email, $token);
    }
    
    
 /**
  * Inicia a consulta através de um código de notificação recebido
  * 
  * @param string $tipo
  * @param string $codigo
  * @return boolean
  * @since 1.0
  */   
    public function obterDadosTransacao($tipo, $codigo) {
        $tipoNotificacao = new PagSeguroNotificationType($tipo);
    		$strTipo = $tipoNotificacao->getTypeFromValue();
                
                try{
                    if ( $strTipo == 'TRANSACTION' ) {
                        return $this->obterDadosDeNotificacao($codigo);
                    }
                } catch (PagSeguroServiceException $e) {
                    echo $e->getMessage();
                    exit();
                }
                
                return false;
    }
    
    
 /**
  * Retorna os dados do comprador
  * 
  * @return array
  * @since 1.0
  */   
    public function obterDadosUsuario() {
        $contato = $this->dadosTransacao->getSender();
        $endereco = $this->dadosTransacao->getShipping()->getAddress();
        $dadosUsuario = 
            array(  'nome' => $contato->getName(),
                    'email' => $contato->getEmail(),
                    'telefoneCompleto' => $contato->getPhone()->getAreaCode().' '.$contato->getPhone()->getNumber(),
                    'codigoArea' => $contato->getPhone()->getAreaCode(),
                    'numeroTelefone' => $contato->getPhone()->getNumber(),
                    'endereco' => $endereco->getStreet(),
                    'numero' => $endereco->getNumber(),
                    'complemento' => $endereco->getComplement(),
                    'bairro' => $endereco->getDistrict(),
                    'cidade' => $endereco->getCity(),
                    'cep' => $endereco->getPostalCode(),
                    'uf' => $endereco->getState(),
                    'pais' => $endereco->getCountry()
             ) ;
        return $dadosUsuario;
    }
    
        /**
  * Retorna o id do status da transação pesquisada
  * 
  * @return array
  * @since 1.0
  */   
    public function obterIdStatusTransacao() {
        return $this->dadosTransacao->getStatus()->getValue();
    }
    
    
    /**
  * Retorna em modo de array o status da transação pesquisada
  * 
  * @return array
  * @since 1.0
  */   
    public function obterStatusTransacao() {
        return Codes::obterStatusTransacao($this->dadosTransacao->getStatus()->getValue());
    }
    
    
 /**
  * Retorna tipo e meio de pagamento
  * 
  * @return array
  * @since 1.0
  */   
    public function obterDadosPagamento() {
        return array(
            'tipo' => PagSeguroTiposPagamento::tipoDePagamentoEmString($this->dadosTransacao->getPaymentMethod()->getType()->getValue()),
            'metodo' => PagSeguroTiposPagamento::meioDePagamentoEmString($this->dadosTransacao->getPaymentMethod()->getCode()->getValue()),
        );
    }
    
    
 /**
  * Retorna um array contendo a data em forma iso para o banco de dados
  * e em ptBR para exibição somente
  * 
  * @return array
  * @since 1.0
  */   
    public function obterDataTransacao() {
        return array(
            'data' => $this->dadosTransacao->getDate(),
            'ultimaAlteracao' => $this->dadosTransacao->getLastEventDate()
        );
    }
    
    
 /**
  * obtervalores method
  * 
  * @return array
  * @since 1.1
  */   
    public function obterValores() {
        foreach($this->dadosTransacao->getItems() as $item) {
            $itens[] = array(   
              'id' => $item->getId(), 
              'descricao' => $item->getDescription() , 
              'quantidade' => $item->getQuantity(),
              'valorUnitario' => $item->getAmount(),
              'peso' => $item->getDescription(),
              'frete' => $item->getShippingCost()
          );
        }
        
        $dados = array(
            'referencia' => $this->dadosTransacao->getReference(),
            'valorTotal' => $this->dadosTransacao->getGrossAmount(),
            'descontoAplicado' => $this->dadosTransacao->getDiscountAmount(),
            'valorExtra' => $this->dadosTransacao->getExtraAmount(),
            'valorTaxa' => $this->dadosTransacao->getFeeAmount(),
            'produtos' => $itens,
        );
        
        return $dados;
    }
    
    
   /**
   * 
   * @return string
   * @since 1.5
   */
    public function obterCodigoTransacao() {
        return $this->dadosTransacao->getcode();
    }
    
   /**
   * 
   * @return string
   * @since 1.0
   */
    public function obterReferencia() {
        return $this->dadosTransacao->getReference();
    }

    
    /**
     * Obtém o status de uma notificação no PagSeguro
     * 
     * @param string $notificationCode
     * @return boolean
     * @since 1.0
     */   
    private function obterDadosDeNotificacao($notificationCode) {	
    	try {
            $this->dadosTransacao = PagSeguroNotificationService::checkTransaction($this->credenciais, $notificationCode);
            if ( $this->dadosTransacao ) {
                return true;
            }
    	} catch (PagSeguroServiceException $e) {
            echo $e->getMessage();
            exit();
    	}
        
    	return false;
    }
}
