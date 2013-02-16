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
* @license MIT License (http://www.opensource.org/licenses/mit-license.php)
* @version 1.0
* 
* ESTE PLUGIN UTILIZA A API DO PAGSEGURO, DISPONÍVEL EM  https://pagseguro.uol.com.br/v2/guia-de-integracao/tutorial-da-biblioteca-pagseguro-em-php.html
* 
*/

App::uses('PagSeguroLibrary', 'Plugin/PagSeguro/Vendor/PagSeguroLibrary');

class NotificacaoComponent extends Component{
    
    private $loadPagSeguroLibrary = null;
    private $credenciais = null;
    private $dadosTransacao = null;
    
    
    
    public function startup(\Controller $controller) {
        $this->loadPagSeguroLibrary = new PagSeguroLibrary;
        
        // definindo alguns dados padrões
        $config = Configure::read('PagSeguro');
        if ( $config ) {
            $this->credenciais = new PagSeguroAccountCredentials($config['credenciais']['email'], $config['credenciais']['token']);
        }
        parent::startup($controller);
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
  * Inicia a consulta através de um código de notificação recebido
  * 
  * @param string $tipo
  * @param string $codigo
  * @return boolean
  */   
    public function obterDadosTransacao($tipo, $codigo) {
                $tipoNotificacao = new PagSeguroNotificationType($tipo);
    		$strTipo = $tipoNotificacao->getTypeFromValue();
                
                try{
                    if ( $strTipo == 'TRANSACTION' ) {
                        if ( $this->__transactionNotification($codigo) ) {
                            return true;
                        }
                    }
                } catch (PagSeguroServiceException $e) {
                    echo $e->getMessage();
                    exit();
                }
    }
    
    
 /**
  * Retorna os dados do comprador
  * 
  * @return array
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
  * Retorna em modo de array o status da transação pesquisada
  * 
  * @return array
  */   
    public function obterStatusTransacao() {
        $status = $this->dadosTransacao->getStatus()->getValue();
        switch($status) {
           case 1:
               $statusRetorno['id'] = 1;
               $statusRetorno['descricao'] = 'Aguardando pagamento';
               break;
           case 2:
               $statusRetorno['id'] = 2;
               $statusRetorno['descricao'] = 'Em análise';
               break;
           case 3:
               $statusRetorno['id'] = 3;
               $statusRetorno['descricao'] = 'Paga';
               break;
           case 4:
               $statusRetorno['id'] = 4;
               $statusRetorno['descricao'] = 'Disponível';
               break;
           case 5:
               $statusRetorno['id'] = 5;
               $statusRetorno['descricao'] = 'Em disputa';
               break;
           case 6:
               $statusRetorno['id'] = 6;
               $statusRetorno['descricao'] = 'Devolvida';
               break;
           case 7:
               $statusRetorno['id'] = 7;
               $statusRetorno['descricao'] = 'Cancelada';
               break;
           default:
               $statusRetorno['id'] = 0;
               $statusRetorno['descricao'] = 'Não foi possível obter o status';
               break;
        }
        
        return $statusRetorno;
    }
    
    
 /**
  * Retorna tipo e meio de pagamento
  * 
  * @return array
  */   
    public function obterDadosPagamento() {
        $tipoPagamento = $this->dadosTransacao->getPaymentMethod()->getType()->getValue();
        $metodoPagamento = $this->dadosTransacao->getPaymentMethod()->getCode()->getValue();


        switch($tipoPagamento) {
            case 1:
                $dadosPagamento['tipo'] = 'Cartão de crédito';
                break;
            case 2:
                $dadosPagamento['tipo'] = 'Boleto';
                break;
            case 3:
                $dadosPagamento['tipo'] = 'Débito online (TEF)';
                break;
            case 4:
                $dadosPagamento['tipo'] = 'Saldo PagSeguro';
                break;
            case 5:
                $dadosPagamento['tipo'] = 'Oi Paggo';
                break;
            default:
                $dadosPagamento['tipo'] = 'Informação não disponível';
                break;
        }

        switch($metodoPagamento) {
            case 101:
                $dadosPagamento['metodo'] = 'Cartão de crédito Visa';
                break;
            case 102:
                $dadosPagamento['metodo'] = 'Cartão de crédito MasterCard';
                break;
            case 103:
                $dadosPagamento['metodo'] = 'Cartão de crédito American Express';
                break;
            case 104:
                $dadosPagamento['metodo'] = 'Cartão de crédito Dinners';
                break;
            case 105:
                $dadosPagamento['metodo'] = 'Cartão de crédito Hypercard';
                break;
            case 106:
                $dadosPagamento['metodo'] = 'Cartão de crédito Aura';
                break;
            case 107:
                $dadosPagamento['metodo'] = 'Cartão de crédito Elo';
                break;
            case 108:
                $dadosPagamento['metodo'] = 'Cartão de crédito PLENOCard';
                break;
            case 109:
                $dadosPagamento['metodo'] = 'Cartão de crédito PersonalCard';
                break;
            case 110:
                $dadosPagamento['metodo'] = 'Cartão de crédito JCB';
                break;
            case 111:
                $dadosPagamento['metodo'] = 'Cartão de crédito Discover';
                break;
            case 112:
                $dadosPagamento['metodo'] = 'Cartão de crédito BrasilCard';
                break;
            case 113:
                $dadosPagamento['metodo'] = 'Cartão de crédito FORTBRASIL';
                break;
            case 202:
                $dadosPagamento['metodo'] = 'Boleto Santander';
                break;
            case 301:
                $dadosPagamento['metodo'] = 'Débito Online Bradesco';
                break;
            case 302:
                $dadosPagamento['metodo'] = 'Débito Online Itaú';
                break;
            case 304:
                $dadosPagamento['metodo'] = 'Débito Online Banco do Brasil';
                break;
            case 306:
                $dadosPagamento['metodo'] = 'Débito Online Banrisul';
                break;
            case 307:
                $dadosPagamento['metodo'] = 'Débito Online HSBC';
                break;
            case 401:
                $dadosPagamento['metodo'] = 'Saldo PagSeguro';
                break;
            case 501:
                $dadosPagamento['metodo'] = 'Oi Paggo';
                break;
        }
        
        return $dadosPagamento;
    }
    
    
 /**
  * Retorna um array contendo a data em forma iso para o banco de dados
  * e em ptBR para exibição somente
  * 
  * @return array
  */   
    public function obterDataTransacao() {
        $data['iso'] = $this->dadosTransacao->getDate();
        $data['ptBr'] = date('d/m/Y H:i:s', strtotime($data['iso']));
        $data['ultimaTentativaIso'] = $this->dadosTransacao->getLastEventDate();
        $data['ultimaTentativaPtBr'] = date('d/m/Y H:i:s', strtotime($data['ultimaTentativaIso']));
        
        return $data;
    }
    
    
    /**
  * 
  * @param string $notificationCode
  */   
    private function __transactionNotification($notificationCode) {	
    	try {
            if ( $this->dadosTransacao = $transaction = PagSeguroNotificationService::checkTransaction($this->credenciais, $notificationCode) ) {
                return true;
            }
    	} catch (PagSeguroServiceException $e) {
            echo $e->getMessage();
            exit();
    	}
    	
    }
    
}

?>
