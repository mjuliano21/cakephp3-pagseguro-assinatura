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
class RetornoPagSeguroComponent extends PagSeguroComponent {

    private $consultaPorCodigo = null;

    /**
     * 
     * @param \Controller $controller
     * @since 2.1
     */
    public function startup(\Controller $controller) {
        parent::startup($controller);
    }

    /**
     * Realiza a busca através do transaction id vindo do pagseguro via get ou post 
     * em sua URL de retorno
     * 
     * @param string $idTransacao
     * @return object
     * @since 2.1
     */
    public function obterInformacoesTransacao($idTransacao) {
        try {
            $this->consultaPorCodigo = PagSeguroTransactionSearchService::searchByCode($this->credenciais, $idTransacao);
            if ($this->consultaPorCodigo) {
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
     * @since 2.1
     */
    public function obterDadosUsuario() {
        $contato = $this->consultaPorCodigo->getSender();
        $endereco = $this->consultaPorCodigo->getShipping()->getAddress();

        $dadosUsuario = array('nome' => $contato->getName(),
                    'email' => $contato->getEmail(),
                    'telefoneCompleto' => $contato->getPhone()->getAreaCode() . ' ' . $contato->getPhone()->getNumber(),
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
                );

        return $dadosUsuario;
    }

    /**
     * Retorna em modo de array o status da transação pesquisada
     * 
     * @return array
     * @since 2.1
     */
    public function obterStatusTransacao() {
        return Codes::obterStatusTransacao($this->consultaPorCodigo->getStatus()->getValue(), array());
    }

    /**
     * Retorna em modo de array o status da transação pesquisada
     * 
     * @return array
     * @since 2.1
     */
    public function obterReferencia() {
        return $this->consultaPorCodigo->getReference();
    }

    /**
     * Retorna tipo e meio de pagamento
     * 
     * @return array
     * @since 2.1
     */
    public function obterDadosPagamento() {
        $dadosPagamento = array(
            'tipo_id' => $this->consultaPorCodigo->getPaymentMethod()->getType()->getValue(),
            'tipo' => PagSeguroTiposPagamento::tipoDePagamentoEmString(
                    $this->consultaPorCodigo->getPaymentMethod()->getType()->getValue()),
            'metodo_id' => $this->consultaPorCodigo->getPaymentMethod()->getCode()->getValue(),
            'metodo' => PagSeguroTiposPagamento::meioDePagamentoEmString(
                    $this->consultaPorCodigo->getPaymentMethod()->getCode()->getValue())
        );

        return $dadosPagamento;
    }

    /**
     * Retorna um array contendo a data em forma iso para o banco de dados
     * e em pt_BR para exibição somente
     * 
     * @return array
     * @since 2.1
     */
    public function obterDataTransacao() {
        return array(
            'data' => $this->consultaPorCodigo->getDate(),
            'ultimaAlteracao' => $this->consultaPorCodigo->getLastEventDate()
        );
    }

    /**
     * obtervalores method
     * 
     * @return array
     * @since 2.1
     */
    public function obterValores() {
        $itensAdquiridos = $this->consultaPorCodigo->getItems();

        foreach ($itensAdquiridos as $item) {
            $itens[] = array(
                'id' => $item->getId(),
                'descricao' => $item->getDescription(),
                'quantidade' => $item->getQuantity(),
                'valorUnitario' => $item->getAmount(),
                'peso' => $item->getWeight(),
                'frete' => $item->getShippingCost(),
            );
        }

        $dados = array(
            'referencia' => $this->consultaPorCodigo->getReference(),
            'valorTotal' => $this->consultaPorCodigo->getGrossAmount(),
            'descontoAplicado' => $this->consultaPorCodigo->getDiscountAmount(),
            'valorExtra' => $this->consultaPorCodigo->getExtraAmount(),
            'valorTaxa' => $this->consultaPorCodigo->getFeeAmount(),
            'produtos' => $itens,
        );

        return $dados;
    }

}
