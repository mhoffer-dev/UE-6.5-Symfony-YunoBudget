<?php

namespace App\Enum;

enum TypePaiement: string
{
    case CB = 'CB';
    case ESPECES = 'ESPECES';
    case CHEQUE = 'CHEQUE';
    case VIREMENT = 'VIREMENT';
    case PAYPAL = 'PAYPAL';
}