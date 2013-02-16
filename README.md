# PAGSEGURO PLUGIN
_v 1.1_


Facilita a integração de pagamentos via PagSeguro em aplicações desenvolvidas com base no CakePHP 2.x.
O plugin realiza apenas interfaceamento para a API de pagamentos do PAgSeguro, com
isso nem o plugin nem o PagSeguro podem ser responsabilizados por uso em desconformidade
à documentação fornecida pelo PagSeguro <https://pagseguro.uol.com.br/v2/guia-de-integracao/visao-geral.html> 
assim como valores fornecidos. A responsabilidade das corretas informações ao PagSeguro são
estritamente do programador que criará a requisição no fechamento do carrinho de compras.



## INSTALAÇÃO
=============


Zip

    Baixe o plugin, descompacte-o na pasta `app/Plugin`, renomeie a pasta `cake-plugin-pagseguro` para `PagSeguro`
----------------------------------------
Git

    Submodulo
        Na raiz de sua aplicação adicione como submodulo: 
       `git submodule add git@github.com:andrebian/cake-plugin-pagseguro.git app/Plugin/PagSeguro`
        

    Clonando  
    -------------------------------------
        `git clone git@github.com:andrebian/cake-plugin-pagseguro.git`
        Altere o nome da pasta de `cake-plugin-pagseguro` para `PagSeguro` e cole-a na pasta `Plugin` de sua aplicação

---------------------------------------------------------------------------

## CONFIGURAÇÕES
================

### Carregando o plugin
-----------------------

No arquivo `bootstrap.php` adicione o suporte ao plugin:
`CakePlugin::load('PagSeguro');`


### Credenciais
---------------

Você deve possuir uma conta no PagSeguro pois precisará setar as credenciais,
estas credenciais são compostas pelo seu email e o token que deve ser configurado na seção de integração
junto ao PagSeguro.

Tal configuração pode ser feita de duas formas, via `bootstrap` ou no controller desejado.

Arquivo bootstrap.php:
```php <?php
	    ...
	    Configure::write('PagSeguro.credenciais', array(
		  'email' => 'seu email',
		  'token' => 'seu token'
	    ));
```


Controller qualquer onde será montada a finalização da compra:
```php <?php
	    ...
	    $this->Carrinho->setCredenciais('seu email', 'seu token');
```


A configuração das credenciais podem ser definidas no `bootstrap` e alteradas caso necessário em qualquer controller


### Carregando o componente
---------------------------

Agora que você já configurou suas credenciais deve definir no `AppController` ou no controller
que o componente será utilizado

```php public $components = array('PagSeguro.Carrinho');```


caso já possua mais componentes faça-o da seguinte forma
```php public $components = array('Outros componentes.....','PagSeguro.Carrinho');```

--------------------------------------------------------------------------------

## UTILIZAÇÃO
=============

### Requisição de pagamento
---------------------------

Para a realização de uma requisição simples, contendo somente os dados do comprador, 
meio de entrega não definido, não definido tipo de pagamento e valores adicionais siga o modelo abaixo.

No controller que fará o processamento dos itens comprados pelo usuário faça assim:

```php
	<?php
	...

        // definindo suas credenciais
        $this->Carrinho->setCredenciais('seu email', 'seu token');

        // definindo a URL de retorno ao realizar o pagamento (opcional)
        $this->Carrinho->setUrlRetorno('http://andrebian.com');

        // definindo a referência da compra (opcional)
        $this->Carrinho->setReferencia(25);

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

        // e finalmente se os dados estivere corretos, redirecionando ao Pagseguro
        if ($result = $this->Carrinho->finalizaCompra() ) {
            $this->redirect($result);
        }
```

-------------------------------------------------------------------------------

### Consultar transações por código

Esta ação é o ideal para tratar o retorno do pagseguro via post ou get. Atribuindo 
o código em uma variável agora você pode consultar o status da transação.
No controller que receberá o retorno do PagSeguro você deve primeiramente definir 
suas credenciais e com o código de retorno chamar o método `obterInformacoesTransacao` do 
componente `Carrinho`, feito isto basta buscar pelas informações desejadas, sendo elas
de momento:
* Dados do usuário;
* Status da transação;
* Dados de pagamento;
* Data (de origem e última notificação do PagSeguro


```php
            // recebendo o id da transação via GET (URL)
            $idTransacao = $this->params['url']['transaction_id'];
            
            // definindo credenciais caso não tenham sido definidas no bootstrap
            $this->Carrinho->setCredenciais('seu email', 'seu token');
            
            // caso haja dados a exibir...
            if ($this->Carrinho->obterInformacoesTransacao($idTransacao) ) {

                    $dadosUsuario = $this->Carrinho->obterDadosUsuario();
                    
                    $statusTransacao = $this->Carrinho->obterStatusTransacao();
                    
                    $dadosPagamento = $this->Carrinho->obterDadosPagamento();
                    
                    $dataTransacao = $this->Carrinho->obterDataTransacao();


                    // visualizando tudo...
                    debug($dadosUsuario);
                    debug($statusTransacao);
                    debug($dadosPagamento);
                    debug($dataTransacao)
                    
                }```


--------------------------------------------------------------------------

### API de notificação
----------------------

Para utilizar o componente de notificação o mesmo deve ser declarado no `AppController` ou no controller que receberá a notificação.
`public $components = array('Demais componentes....', 'PagSeguro.Notificacao');`


O PagSeguro fornece a opção de configuração de uma URL para o recebimento de notificações.
Tal URL receberá uma requisição em formato POST contendo duas informações:
1 - Tipo da notificação; 2 - Código da notificação

Não se confunda, o código da transação e da notificação são diferentes para uma mesma compra, e a cada
notificação o código se altera.

Modelo recebido pelo Pagseguro:
```php
    POST http://lojamodelo.com.br/notificacao HTTP/1.1
    Host:pagseguro.uol.com.br
    Content-Length:85
    Content-Type:application/x-www-form-urlencoded
    notificationCode=766B9C-AD4B044B04DA-77742F5FA653-E1AB24
    notificationType=transaction```


Com tais dados em mãos você deve realizar a requisição das informações da transação.

No controller/action que receberá tal notificação basta realizar a chamada ao método `obterDadosTransacao` informando o tipo e código de notificação:

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

            
            // agora exibindo todos os resultados
            debug($dadosUsuario);
            debug($statusTransacao);
            debug($dadosPagamento);
            debug($dataTransacao);

        }```

--------------------------------------------------------------------------------



# TODO

### Pagamento
    * definir meios de pagamento
    

### Consultas
    * Consulta por data
    * Consulta por transações abandonadas
    * Exibição de itens e valores da compra



### Testes
Criar testes


Att. Andre Cardoso