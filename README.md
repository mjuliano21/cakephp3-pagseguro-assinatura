[![Latest Stable Version](https://poser.pugx.org/andrebian/pag_seguro/v/stable.png)](https://packagist.org/packages/andrebian/pag_seguro) [![Total Downloads](https://poser.pugx.org/andrebian/pag_seguro/downloads.png)](https://packagist.org/packages/andrebian/pag_seguro) [![Latest Unstable Version](https://poser.pugx.org/andrebian/pag_seguro/v/unstable.png)](https://packagist.org/packages/andrebian/pag_seguro) [![License](https://poser.pugx.org/andrebian/pag_seguro/license.png)](https://packagist.org/packages/andrebian/pag_seguro)
[![githalytics.com alpha](https://cruel-carlota.pagodabox.com/8d4bc51766116b27f682121865660505 "githalytics.com")](http://githalytics.com/andrebian/cake-plugin-pagseguro)

# PAGSEGURO PLUGIN
_v 1.5.1_


Facilita a integração de pagamentos via PagSeguro em aplicações desenvolvidas com base no CakePHP 2.x.
O plugin realiza apenas interfaceamento para a API de pagamentos do PagSeguro, com
isso nem o plugin nem o PagSeguro podem ser responsabilizados por uso em desconformidade
à documentação fornecida pelo PagSeguro <https://pagseguro.uol.com.br/v2/guia-de-integracao/visao-geral.html> 
assim como valores fornecidos. A responsabilidade das corretas informações ao PagSeguro são
estritamente do programador que criará a requisição no fechamento do carrinho de compras.

____________________

## INSTALAÇÃO

Composer
---------------

    {
        "require": {
            "andrebian/pag_seguro": "dev-master"
        }
    }



Git
----

Submodulo
Na raiz de sua aplicação adicione como submodulo: 

    git submodule add git@github.com:andrebian/cake-plugin-pagseguro.git app/Plugin/PagSeguro
      
        

Clonando 

    git clone git@github.com:andrebian/cake-plugin-pagseguro
    
Altere o nome da pasta de `cake-plugin-pagseguro` para `PagSeguro` e cole-a na pasta `Plugin` de sua aplicação

Zip
----

Baixe o plugin, descompacte-o na pasta `app/Plugin`, renomeie a pasta `cake-plugin-pagseguro` para `PagSeguro`

_________________________

## CONFIGURAÇÕES


### Carregando o plugin

No arquivo `bootstrap.php` adicione o suporte ao plugin:
`CakePlugin::load('PagSeguro');` ou `CakePlugin::loadAll();`


### Credenciais

Você deve possuir uma conta no PagSeguro pois precisará setar as credenciais,
estas credenciais são compostas pelo seu email e o token que deve ser configurado na seção de integração
junto ao PagSeguro.

Tal configuração pode ser feita de duas formas, via `bootstrap` ou no controller desejado.

Arquivo bootstrap

    
        
        Configure::write('PagSeguro.credenciais', array(
            'email' => 'email cadastrado',
            'token' => 'token gerado'
        ));
        


Controller qualquer onde será montada a finalização da compra

    $this->carrinho->setCredenciais('email cadastrado', 'token gerado');



A configuração das credenciais podem ser definidas no `bootstrap` e alteradas caso necessário em qualquer controller.


### Carregando o componente


Agora que você já configurou suas credenciais deve definir no `AppController` ou no controller
que o componente será utilizado

    public $components = array('PagSeguro.Carrinho');



Caso já possua mais componentes faça-o da seguinte forma

    public $components = array('Outros componentes', 'PagSeguro.Carrinho');



## UTILIZAÇÃO


* Requisição de pagamento: Leia o arquivo REQUISICAO_PAGAMENTO
* Retorno de requisição de pagamento: Leia o arquivo CONSULTA
* Consulta por código: Leia o arquivo CONSULTA
* Consulta por perído: Leia o arquivo CONSULTA
* Consulta por transações abandonadas: Leia o arquivo CONSULTA
* Notificações: Leia o arquivo NOTIFICACAO

______________


##TODO

* Testes


##PROBLEMAS

Reporte-os por aqui https://github.com/andrebian/cake-plugin-pagseguro/issues?state=open

##CONTRIBUIDORES

* Marcelo Siqueira - https://github.com/marcelosiqueira


##ATENÇÃO
Muitos desenvolvedores utilizaram meu email pessoal que estava nos exemplos para realizar compras, peço humildemente que por favor, use SEU email para realizar os testes de compra, já ajudei com o plugin não posso ajudar verificando emails e dando reports.

Muito agradecido.


