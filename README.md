# PAGSEGURO PLUGIN
v 1.0


Facilita a integração de pagamentos via PagSeguro em aplicações desenvolvidas com base no CakePHP 2.x


----------------------------------------------------------------------------

## INSTALAÇÃO

#### Download

Zip:
    Baixe o plugin, descompacte-o na pasta `app/Plugin`, renomeie a pasta `cake-plugin-pagseguro` para `PagSeguro`

Git: 
    Submodulo: Na raiz de sua aplicação adicione como submodulo
        ```php git submodule add git@github.com:andrebian/cake-plugin-pagseguro.git app/Plugin/PagSeguro
        ```

    Clonando:  
        git clone git@github.com:andrebian/cake-plugin-pagseguro.git
        Altere o nome da pasta de `cake-plugin-pagseguro` para `PagSeguro` e cole-a na pasta `Plugin` de sua aplicação

---------------------------------------------------------------------------

## UTILIZAÇÃO

Primeiramente no arquivo `bootstrap.php` adicione o suporte ao plugin:
```php
    CakePlugin::load('PagSeguro');
```


Em seguida no arquivo `Controller/AppController.php` ou no controller desejado defina o seguinte:

```php
    public $components = array('PagSeguro.Carrinho');
```


Para a realização de uma requisição simples, contendo somente os dados do comprador, 
meio de entrega não definido, não definido tipo de pagamento e valores adicionais siga o modelo abaixo.

No controller que fará o processamento dos itens comprados pelo usuário faça assim:

```php

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



# Consultar transações por código

Esta ação é o ideal para tratar o retorno do pagseguro via post ou get. Atribuindo 
o código em uma variável agora você pode consultar o status da transação.
No controller que receberá o retorno do PagSeguro você deve primeiramente definir 
suas credenciais e com o código de retorno chamar o método `obterInformacoesTransacao` do 
componente `Carrinho`, feito isto basta buscar pelas informações desejadas, sendo elas
de momento:
** Dados do usuário;
** Status da transação;
** Dados de pagamento;
** Data (de origem e última notificação do PagSeguro


```php
            // recebendo o id da transação 
            $idTransacao = $this->request->data['code'];
            
            // definindo credenciais
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
                    
                }

```


--------------------------------------------------------------------------

# TODO

## Pagamento
Itens adicionais para compra como
    * Valor extra
    * MaxAge (validade da requisição)
    * MaxUses (Quantidade de requisições para itens fixos)
    * Retorno de status logo após a finalização da compra



## Notificações
Integração com notificações


## Testes
Criar testes