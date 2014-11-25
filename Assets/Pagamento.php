<?php

class Pagamento
{
    const TIPO_PAGAMENTO_CARTAO_DE_CREDITO = 1;
    const TIPO_PAGAMENTO_BOLETO = 2;
    const TIPO_PAGAMENTO_DEBITO_ONLINE = 3;
    const TIPO_PAGAMENTO_SALDO_PAGSEGURO = 4;
    const TIPO_PAGAMENTO_OI_PAGGO = 5;
    

    public static $tiposPagamento = array(
        self::TIPO_PAGAMENTO_CARTAO_DE_CREDITO => 'Cartão de crédito',
        self::TIPO_PAGAMENTO_BOLETO => 'Boleto',
        self::TIPO_PAGAMENTO_DEBITO_ONLINE => 'Débito online (TEF)',
        self::TIPO_PAGAMENTO_SALDO_PAGSEGURO => 'Saldo PagSeguro',
        self::TIPO_PAGAMENTO_OI_PAGGO => 'Oi Paggo'
    );
    
    
    public static $meiosPagamento = array(
        101 => 'Cartão de crédito Visa',
        102 => 'Cartão de crédito MasterCard',
        103 => 'Cartão de crédito American Express',
        104 => 'Cartão de crédito Dinners',
        105 => 'Cartão de crédito Hypercard',
        106 => 'Cartão de crédito Aura',
        107 => 'Cartão de crédito Elo',
        108 => 'Cartão de crédito PLENOCard',
        109 => 'Cartão de crédito PersonalCard',
        110 => 'Cartão de crédito JCB',
        111 => 'Cartão de crédito Discover',
        112 => 'Cartão de crédito BrasilCard',
        113 => 'Cartão de crédito FORTBRASIL',
        202 => 'Boleto Santander',
        301 => 'Débito Online Bradesco',
        302 => 'Débito Online Itaú',
        304 => 'Débito Online Banco do Brasil',
        306 => 'Débito Online Banrisul',
        307 => 'Débito Online HSBC',
        401 => 'Saldo PagSeguro',
        501 => 'Oi Paggo'
    );

}
