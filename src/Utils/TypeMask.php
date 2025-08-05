<?php

namespace Core\Utils;

enum TypeMask: string
{
    case CPF = 'cpf';
    case TELEFONE = 'telefone';
    case EMAIL = 'email';
}