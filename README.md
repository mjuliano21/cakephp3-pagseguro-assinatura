# PAGSEGURO PLUGIN
_v 1.1_


Facilita a integração de pagamentos via PagSeguro em aplicações desenvolvidas com base no CakePHP 2.x.
O plugin realiza apenas interfaceamento para a API de pagamentos do PagSeguro, com
isso nem o plugin nem o PagSeguro podem ser responsabilizados por uso em desconformidade
à documentação fornecida pelo PagSeguro <https://pagseguro.uol.com.br/v2/guia-de-integracao/visao-geral.html> 
assim como valores fornecidos. A responsabilidade das corretas informações ao PagSeguro são
estritamente do programador que criará a requisição no fechamento do carrinho de compras.



## INSTALAÇÃO
=============


Zip

    Baixe o plugin, descompacte-o na pasta `app/Plugin`, renomeie a pasta `cake-plugin-pagseguro` para `PagSeguro`

Git

    Submodulo
        Na raiz de sua aplicação adicione como submodulo: 
       `git submodule add git@github.com:andrebian/cake-plugin-pagseguro.git app/Plugin/PagSeguro`
        

    Clonando  
        `git clone git@github.com:andrebian/cake-plugin-pagseguro.git`
        Altere o nome da pasta de `cake-plugin-pagseguro` para `PagSeguro` e cole-a na pasta `Plugin` de sua aplicação



## CONFIGURAÇÕES
================

### Carregando o plugin

No arquivo `bootstrap.php` adicione o suporte ao plugin:
`CakePlugin::load('PagSeguro');` ou `CakePlugin::loadAll();`


### Credenciais

Você deve possuir uma conta no PagSeguro pois precisará setar as credenciais,
estas credenciais são compostas pelo seu email e o token que deve ser configurado na seção de integração
junto ao PagSeguro.

Tal configuração pode ser feita de duas formas, via `bootstrap` ou no controller desejado.

Arquivo bootstrap
`<?php
	    ...
	    Configure::write('PagSeguro.credenciais', array(
		  'email' => 'seu email',
		  'token' => 'seu token'
	    )); `

Controller qualquer onde será montada a finalização da compra
` <?php
	    $this->Carrinho->setCredenciais('seu email', 'seu token'); `


A configuração das credenciais podem ser definidas no `bootstrap` e alteradas caso necessário em qualquer controller


### Carregando o componente


Agora que você já configurou suas credenciais deve definir no `AppController` ou no controller
que o componente será utilizado

```php public $components = array('PagSeguro.Carrinho'); ```


caso já possua mais componentes faça-o da seguinte forma
```php public $components = array('Outros componentes.....','PagSeguro.Carrinho');```



## UTILIZAÇÃO
=============

### Requisição de pagamento


Para a realização de uma requisição simples, contendo somente os dados do comprador, 
meio de entrega não definido, não definido tipo de pagamento e valores adicionais siga o modelo abaixo.

No controller que fará o processamento dos itens comprados pelo usuário deverá se parecer com o exemplo abaixo:

```php
	<?php
	...

        // definindo suas credenciais
        $this->Carrinho->setCredenciais('seu email', 'seu token');

        // definindo a URL de retorno ao realizar o pagamento (opcional)
        $this->Carrinho->setUrlRetorno('http://andrebian.com');

        // definindo a referência da compra (opcional)
        $this->Carrinho->setReferencia(25);

        /**
        *   adicionarItem method
        *   @param int id  OBRIGATÓRIO
        *   @param string descricao OBRIGATÓRIO
        *   @param string valorUnitario (formato 0.00 -> ponto como separador 
        *       de centavos, e nada separando milhares) OBRIGATÓRIO
        *   @param string peso (formato em gramas ex: 1KG = 1000) OBRIGATÓRIO
        *   @param int quantidade OBRIGATÓRIO (padrão 1 unidade)
        *   @param string frete (valor do frete formato 0.00 -> ponto como separador
        *       de centavos e nada separando milhares) OPCIONAL
        */


        // para adicionar apenas 1 item:
        $this->Carrinho->adicionarItem(1, 'Produto Teste', '25.00', '1000', 1);


        // para adicionar vários itens
        // Opção 1:
        $this->Carrinho->adicionarItem(1, 'Produto Teste', '25.00', '1000', 1);
        $this->Carrinho->adicionarItem(2, 'Produto Teste 2', '12.40', '1000', 1);
        $this->Carrinho->adicionarItem(3, 'Produto Teste 3', '27.90', '1000', 1);
        
        // OPção 2:
        $cont = 1;
        foreach($itensSelecionados as $itens) {
            $this->Carrinho->adicionarItem($cont, $itens['nomeProduto'], $itens['precoProduto'], $itens['precoProduto'], $itens['quantidadeProduto']);
            $cont++;
        }

        // definindo o contato do comprador
        $this->Carrinho->setContatosComprador('Andre Cardoso', 'andrecardosodev@gmail.com', '41', '00000000');

        // definindo o endereço do comprador
        $this->Carrinho->setEnderecoComprador('00000000', 'Rua Teste', '1234', 'Complemento', 'Bairro', 'Cidade', 'UF');

        
        /**
        *   setTipoFrete method OPCIONAL
        *
        *   @param tipoFrete (PAC, SEDEX, NAO_ESPECIFICADO)
        *
        */
        $this->Carrinho->setTipoFrete('SEDEX');


        /**
        *   setValorFrete method OPCIONAL
        *
        *   @param string valorTotalFrete (formato 0.00 -> ponto como separador de
        *       centavos e nada separando milhares)
        *
        */
        $this->Carrinho->setValorTotalFrete('32.00');


        /**
        *   tipoPagamento method OPCIONAL
        *   
        *   @param string tipoPagamento (CREDIT_CARD, BOLETO, ONLINE_TRANSFER, BALANCE, OI_PAGGO)
        *
        */
        $this->Carrinho->setTipoPagamento('BOLETO');


        // e finalmente se os dados estivere corretos, redirecionando ao Pagseguro
        if ($result = $this->Carrinho->finalizaCompra() ) {
            $this->redirect($result);
        }
```



### Consultar transações por código

Esta ação é o ideal para tratar o retorno do pagseguro via GET. Atribuindo 
o código em uma variável agora você pode consultar o status da transação.
No controller que receberá o retorno do PagSeguro você deve primeiramente definir 
suas credenciais e com o código de retorno chamar o método `obterInformacoesTransacao` do 
componente `Carrinho`, feito isto basta buscar pelas informações desejadas, sendo elas:
* Dados do usuário;
* Status da transação;
* Dados de pagamento;
* Data (de origem e última notificação do PagSeguro)
* Dados dos produtos comprados

As informações acima são idênticas para a API de notificação


```php
            // recebendo o id da transação via GET (URL)
            $idTransacao = $this->params['url']['transaction_id'];
            
            // definindo credenciais caso não tenham sido definidas no bootstrap
            $this->Carrinho->setCredenciais('seu email', 'seu token');
            
            // caso haja dados a exibir...
            if ($this->Carrinho->obterInformacoesTransacao($idTransacao) ) {

                    // retorna os dados do usuário
                    $dadosUsuario = $this->Carrinho->obterDadosUsuario();
                    
                    // retorna o status da transação
                    $statusTransacao = $this->Carrinho->obterStatusTransacao();
                    
                    // retorna os dados de pagamento    
                    $dadosPagamento = $this->Carrinho->obterDadosPagamento();
                    
                    // retorna a data de compra e última interação
                    $dataTransacao = $this->Carrinho->obterDataTransacao();

                    // retorna detalhes dos produtos e valores
                    $produtos = $this->Carrinho->obterValores();


                    // agora exibindo todos os resultados

                    debug($dadosUsuario);
                    /*
                    array(
                        'nome' => 'Andre Cardoso',
                        'email' => 'andrecardosodev@gmail.com',
                        'telefoneCompleto' => '41 00000000',
                        'codigoArea' => '41',
                        'numeroTelefone' => '00000000',
                        'endereco' => 'Rua Teste',
                        'numero' => '1234',
                        'complemento' => 'Complemento teste',
                        'bairro' => 'Centro',
                        'cidade' => 'Curitiba',
                        'cep' => '80000000',
                        'uf' => 'PR',
                        'pais' => 'BRA'
                    )
                    */


                    debug($statusTransacao);
                    /*
                    array(
                        'id' => (int) 7,
                        'descricao' => 'Cancelada'
                    )
                    */


                    debug($dadosPagamento);
                    /*
                    array(
                        'tipo' => 'Boleto',
                        'metodo' => 'Boleto Santander'
                    )
                    */


                    debug($dataTransacao);
                    /*
                    array(
                        'iso' => '2012-11-24T13:14:41.000-02:00',
                        'ptBr' => '24/11/2012 13:14:41',
                        'ultimaTentativaIso' => '2012-12-08T07:34:15.000-02:00',
                        'ultimaTentativaPtBr' => '08/12/2012 07:34:15'
                    )    
                    */

                    
                    debug($produtos);
                    /*
                    array(
                        'valorTotal' => '0.01',
                        'descontoAplicado' => '0.00',
                        'valorExtra' => '0.00',
                        'produtos' => array(
                                (int) 0 => array(
                                        'id' => '1',
                                        'descricao' => 'Produto Teste',
                                        'quantidade' => '1',
                                        'valorUnitario' => '0.01',
                                        'peso' => '1000',
                                        'frete' => null
                                )
                        )
                    )
                    */
                    
                } 
```




### API de notificação


Para utilizar o componente de notificação o mesmo deve ser declarado no `AppController` ou no controller que receberá a notificação.
`public $components = array('Demais componentes....', 'PagSeguro.Notificacao');`


O PagSeguro fornece a opção de configuração de uma URL para o recebimento de notificações.
Tal URL receberá uma requisição em formato POST contendo duas informações:
1 - Tipo da notificação; 2 - Código da notificação

Não se confunda, o código da transação e da notificação são diferentes para uma mesma compra, e a cada
notificação o código se altera.

Modelo recebido pelo Pagseguro:
`
    POST http://lojamodelo.com.br/notificacao HTTP/1.1
    Host:pagseguro.uol.com.br
    Content-Length:85
    Content-Type:application/x-www-form-urlencoded
    notificationCode=766B9C-AD4B044B04DA-77742F5FA653-E1AB24
    notificationType=transaction `


Com tais dados em mãos você deve realizar a requisição das informações da transação.

No controller/action que receberá tal notificação basta realizar a chamada ao método `obterDadosTransacao` informando o tipo e código de notificação

```php
        $tipo = $this->request->data['notificationType'];
        $codigo = $this->request->data['notificationCode'];

        if ( $this->Notificacao->obterDadosTransacao($tipo, $codigo) ) {
            // retorna somente os dados do comprador
            $dadosUsuario = $this->Notificacao->obterDadosUsuario(); 

            // retorna o status da transação 
            $statusTransacao = $this->Notificacao->obterStatusTransacao();

            // retorna os dados de pagamento (tipo de pagamento e forma de pagamento)
            $dadosPagamento = $this->Notificacao->obterDadosPagamento();

            // retorna a data que a compra foi realizada e última notificação
            $dataTransacao = $this->Notificacao->obterDataTransacao();

            // retorna os dados de produtos comprados
            $produtos = $this->Notificacao->obterValores()
            

            // agora exibindo todos os resultados

            debug($dadosUsuario);
            /*
            array(
                'nome' => 'Andre Cardoso',
                'email' => 'andrecardosodev@gmail.com',
                'telefoneCompleto' => '41 00000000',
                'codigoArea' => '41',
                'numeroTelefone' => '00000000',
                'endereco' => 'Rua Teste',
                'numero' => '1234',
                'complemento' => 'Complemento',
                'bairro' => 'Centro',
                'cidade' => 'Curitiba',
                'cep' => '80000000',
                'uf' => 'PR',
                'pais' => 'BRA'
            )
            */


            debug($statusTransacao);
            /*
            array(
                'id' => (int) 1,
                'descricao' => 'Aguardando pagamento'
            )    
            */


            debug($dadosPagamento);
            /*
            array(
                'tipo' => 'Boleto',
                'metodo' => 'Boleto Santander'
            )
            */


            debug($dataTransacao);
            /*
            array(
                'iso' => '2013-02-16T19:35:53.000-02:00',
                'ptBr' => '16/02/2013 19:35:53',
                'ultimaTentativaIso' => '2013-02-16T19:36:00.000-02:00',
                'ultimaTentativaPtBr' => '16/02/2013 19:36:00'
            )
            */


            debug($produtos);
            /*
            array(
                'valorTotal' => '0.01',
                'descontoAplicado' => '0.00',
                'valorExtra' => '0.00',
                'produtos' => array(
                        (int) 0 => array(
                                'id' => '1',
                                'descricao' => 'Produto Teste',
                                'quantidade' => '1',
                                'valorUnitario' => '0.01',
                                'peso' => '1000',
                                'frete' => null
                        )
                )
            )
            */


        }
```

====================



# TODO

### Pagamento
    * definir meios de pagamento
    

### Consultas
    * Consulta por data
    * Consulta por transações abandonadas



### Testes
Criar testes


Att. Andre Cardoso