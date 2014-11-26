<?php

App::uses('AppController', 'Controller');

class ComprasController extends AppController {

        public $components = array('PagSeguro.PagSeguro', 'PagSeguro.Checkout', 'PagSeguro.Notificacao', 'PagSeguro.Consulta', 'PagSeguro.RetornoPagSeguro');
        
  /**
   * Processa os produtos e envia a requisição ao PagSeguro
   * 
   */      
        public function checkout() {
                // caso não tenha definido as credenciais no bootstrap descomente a linha abaixo
                // e defina seus dados
                //$this->Checkout->defineCredenciais('seu-email', 'seu-token');
                
            
                // opcionais
                //$this->Checkout->defineUrlRetorno('url-de-retorno');
                //$this->Checkout->defineReferencia(25);
                
                
                $this->Checkout->adicionarItem(1, 'Produto Teste', '0.01', '1000', 1);
                $this->Checkout->adicionarItem(2, 'Produto Teste 2', '12.40', '1000', 1);
                $this->Checkout->adicionarItem(3, 'Produto Teste 3', '27.90', '1000', 1);
                $this->Checkout->defineContatosComprador('nome do comprador', 'email-do-comprador', '41', '99999999');
                $this->Checkout->defineEnderecoComprador('80000000', 'Rua Teste', '0000', 'Complemento', 'Bairro', 'Cidade', 'UF');
                
                
                $this->Checkout->defineTipoFrete(PagSeguroEntrega::TIPO_SEDEX);
                
                // opcional
                $this->Checkout->defineValorTotalFrete('0.01');
                
                
                $this->Checkout->defineTipoPagamento(
                    PagSeguroTiposPagamento::tipoDePagamentoEmString(
                        PagSeguroTiposPagamento::TIPO_PAGAMENTO_BOLETO
                    )
                );
                
                
                if ($result = $this->Checkout->finalizaCompra() ) {
                    $this->redirect($result);
                }
        }
        
        
 /**
  * Utilizado para retorno e consulta por código
  */       
        public function retorno() {
            $idTransacao = $this->params['url']['transaction_id'];
                
            if ($this->RetornoPagSeguro->obterInformacoesTransacao($idTransacao) ) {
                $dadosUsuario = $this->RetornoPagSeguro->obterDadosUsuario();
                debug($dadosUsuario);
                
                $statusTransacao = $this->RetornoPagSeguro->obterStatusTransacao();
                debug($statusTransacao);
                
                $dadosPagamento = $this->RetornoPagSeguro->obterDadosPagamento();
                debug($dadosPagamento);
                
                $dataTransacao = $this->RetornoPagSeguro->obterDataTransacao();
                debug($dataTransacao);
                
                $valores = $this->RetornoPagSeguro->obterValores();
                debug($valores);
            }
        }
        
        
 /**
  * Retorna as transações por período
  * Informe a data final que a inicial é automaticamente setada para 1 mês antes
  */       
        public function consultaPorPeriodo() {
            if ( $consulta = $this->Consulta->obterTransacoesPorPeriodo(date('Y-m-d')) ) {
                debug($consulta);
            }
        }
        
 
 /**
  * Retorna as transações abandonadas por período
  * Informe a data final que a inicial é automaticamente setada para 1 mês antes
  */        
        public function consultaTransacoesAbandonadas() {
            if ( $consulta = $this->Consulta->obterTransacoesAbandonadas(date('Y-m-d')) ) {
                debug($consulta);
            }
        }
    
}
