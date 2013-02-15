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

App::uses('PagSeguroLibrary', 'Plugin/PagSeguro/Vendor/PagSeguroLibrary');

class CarrinhoComponent extends Component{
    
    private $loadPagSeguroLibrary = null;
    private $credenciais = null;
    private $montaPagamento = null;
    private $consultaPorCodigo = null;
    private $comprador = null;
    private $Controller = null;
    
   
/**
 * 
 * @param \Controller $Controller
 */    
    public function startup(\Controller $Controller) {
        
        // Instanciando classes para gerar o pagamento
        $this->loadPagSeguroLibrary = new PagSeguroLibrary;
        $this->montaPagamento = new PagSeguroPaymentRequest;
        $this->comprador = new PagSeguroSender;
        
        
        // definindo alguns dados padrões
        $config = Configure::read('PagSeguro');
        if ( $config ) {
            $this->credenciais = new PagSeguroAccountCredentials($config['credenciais']['email'], $config['credenciais']['token']);
        }
        
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
  * Define um valor extra, ponto flutuante com 2 casas decimais
  * Pode ser valor positivo para acréscimo ou negativo para desconto
  * 
  * @param float $valor
  */   
    public function setValorExtra($valor) {
        $this->montaPagamento->setExtraAmount($valor);
    }
    
    
 /**
  * Define em segundos por quanto tempo a requisição será válida
  * 
  * @param int $validade
  */   
    public function setValidadeRequisicao($validade) {
        $this->montaPagamento->setMaxAge($validade);
    }
    
    
 /**
  * Define a quantidade de vezes que a requisição será utilizada
  * Útil para produtos ou taxas não variáveis
  * 
  * @param int $quantidade
  */   
    public function setQuantidadeUso($quantidade) {
        $this->montaPagamento->setMaxUses($quantidade);
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
        $this->montaPagamento->setSender($nome, $email, $codigoArea, $numeroTelefone);
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
        try {
            if ($url = $this->montaPagamento->register($this->credenciais) ) {
                return $url;
            }
        } catch (PagSeguroServiceException $e) {
            echo $e->getMessage();
            exit();
        }
    }
    
    
    ###############
    # MÓDULO PARA TRATAR DO RETORNO
    
    
 /**
  * Realiza a busca através do transaction id vindo do pagseguro via get ou post 
  * em sua URL de retorno
  * 
  * @param string $idTransacao
  * @return object
  */    
    public function obterInformacoesTransacao($idTransacao) {
        try{
            if ($this->consultaPorCodigo = PagSeguroTransactionSearchService::searchByCode($this->credenciais, $idTransacao) ) {
                return true;
            }
        } catch (PagSeguroServiceException $e) {
            echo $e->getMessage();
            exit();
        }
    }
    
    
 /**
  * Retorna os dados do usuário após a consulta
  * 
  * @return array
  */   
    public function obterDadosUsuario() {
        $contato = $this->consultaPorCodigo->getSender();
        $endereco = $this->consultaPorCodigo->getShipping();
        
        $usuario['nome'] = $contato->getName();
        $usuario['email'] = $contato->getEmail();
        $usuario['telefoneCompleto'] = $contato->getPhone()->getAreaCode() . ' ' . $contato->getPhone()->getNumber();
        $usuario['ddd'] = $contato->getPhone()->getAreaCode();
        $usuario['numeroTelefone'] = $contato->getPhone()->getNumber();
        $usuario['endereco'] = $endereco->getAddress()->getStreet();
        $usuario['numero'] = $endereco->getAddress()->getNumber();
        $usuario['complemento'] = $endereco->getAddress()->getComplement();
        $usuario['bairro'] = $endereco->getAddress()->getDistrict();
        $usuario['cidade'] = $endereco->getAddress()->getCity();
        $usuario['uf'] = $endereco->getAddress()->getState();
        $usuario['cep'] = $endereco->getAddress()->getPostalCode();
        $usuario['pais'] = $endereco->getAddress()->getCountry();
        
        return $usuario;
    }
    
 
 /**
  * Retorna em modo de array o status da transação pesquisada
  * 
  * @return array
  */   
    public function obterStatusTransacao() {
        $status = $this->consultaPorCodigo->getStatus()->getValue();
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
        $tipoPagamento = $this->consultaPorCodigo->getPaymentMethod()->getType()->getValue();
        $metodoPagamento = $this->consultaPorCodigo->getPaymentMethod()->getCode()->getValue();


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
        $data['iso'] = $this->consultaPorCodigo->getDate();
        $data['ptBr'] = date('d/m/Y H:i:s', strtotime($data['iso']));
        $data['ultimaTentativaIso'] = $this->consultaPorCodigo->getLastEventDate();
        $data['ultimaTentativaPtBr'] = date('d/m/Y H:i:s', strtotime($data['ultimaTentativaIso']));
        
        return $data;
    }
    
    
}