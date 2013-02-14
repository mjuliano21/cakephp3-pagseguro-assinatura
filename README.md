# PAGSEGURO PLUGIN

Facilita a integração de pagamentos via PagSeguro em aplicações desenvolvidas com base no CakePHP 2.x


----------------------------------------------------------------------------

## INSTALAÇÃO

#### Download
Baixe o via git, http ou zip e adicione na pasta `app/Plugin`




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
