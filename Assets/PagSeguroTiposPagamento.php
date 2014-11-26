<?php

class PagSeguroTiposPagamento
{
    const TIPO_PAGAMENTO_CARTAO_DE_CREDITO = 1;
    const TIPO_PAGAMENTO_BOLETO = 2;
    const TIPO_PAGAMENTO_DEBITO_ONLINE = 3;
    const TIPO_PAGAMENTO_SALDO_PAGSEGURO = 4;
    const TIPO_PAGAMENTO_OI_PAGGO = 5;
    const TIPO_PAGAMENTO_DEPOSITO_DIRETO = 7;
    
    public static $tiposPagamento = array(
        self::TIPO_PAGAMENTO_CARTAO_DE_CREDITO => 'CREDIT_CARD',
        self::TIPO_PAGAMENTO_BOLETO => 'BOLETO',
        self::TIPO_PAGAMENTO_DEBITO_ONLINE => 'ONLINE_TRANSFER',
        self::TIPO_PAGAMENTO_SALDO_PAGSEGURO => 'BALANCE',
        self::TIPO_PAGAMENTO_OI_PAGGO => 'OI_PAGGO',
        self::TIPO_PAGAMENTO_DEPOSITO_DIRETO => 'DIRECT_DEPOSIT',
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
    
    /**
     * @param int $tipo
     * @return string
     * @throws NotFoundException
     */
    public static function tipoDePagamentoEmString($tipo)
    {
        if( array_key_exists($tipo, self::$tiposPagamento) ) {
            return self::$tiposPagamento[$tipo];
        }
        
        throw new NotFoundException('O tipo de pagamento com ID: ' . $tipo . ' não foi localizado');
    }
    
    /**
     * 
     * @param int $id
     * @return string
     * @throws NotFoundException
     */
    public static function meioDePagamentoEmString($id) 
    {
        if( array_key_exists($id, self::$meiosPagamento) ) {
            return self::$meiosPagamento[$id];
        }
        
        throw new NotFoundException('O meio de pagamento com ID: ' . $id . ' não foi localizado');
    }

}
